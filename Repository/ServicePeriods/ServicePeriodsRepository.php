<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Services\Repository\ServicePeriods;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Orders\Order\Entity\Event\OrderEvent;
use BaksDev\Orders\Order\Entity\Order;
use BaksDev\Orders\Order\Entity\Services\OrderService;
use BaksDev\Orders\Order\Type\OrderService\Service\ServiceUid;
use BaksDev\Orders\Order\Type\Status\OrderStatus;
use BaksDev\Services\Entity\Event\Invariable\ServiceInvariable;
use BaksDev\Services\Entity\Event\Period\ServicePeriod;
use BaksDev\Services\Entity\Event\Price\ServicePrice;
use BaksDev\Services\Entity\Event\ServiceEvent;
use BaksDev\Services\Entity\Service;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use DateTimeImmutable;
use Generator;

final class ServicePeriodsRepository implements ServicePeriodsInterface
{
    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly UserProfileTokenStorageInterface $UserProfileTokenStorage,
    ) {}

    /** Фильтр по профилю */
    public function onProfile(UserProfileUid $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /** Убирает дублирующийся неактивный период и добавляет данные order_service */
    private function prepareOrderService(array $periods): array
    {

        $result = [];

        /* Получить все уникальные (по "Время от") периоды */
        foreach($periods as $period)
        {
            $periodFrm = new DateTimeImmutable($period['time_from'])->format('H-m-i');
            $result[$periodFrm] = $period;
        }


        /* Заполнить периоды данными по order_service */
        foreach($periods as $period)
        {

            $periodFrm = new DateTimeImmutable($period['time_from'])->format('H-m-i');

            if($period['order_services'] != null)
            {
                $order_services = json_decode($period['order_services'], null, 512, JSON_THROW_ON_ERROR);
                $result[$periodFrm]['order_services_data'][$period['id']] = $order_services;
            }
            else
            {
                $result[$periodFrm]['order_services_data'][$period['id']] = [];
            }

        }

        return $result;
    }


    public function findAll(ServiceUid $serviceUid): false|Generator
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();


        $dbal->from(Service::class, 'service')
            ->addSelect('service.id as service_id');

        $dbal->where('service.id = :serv')
            ->setParameter(
                key:'serv',
                value: $serviceUid,
                type: ServiceUid::TYPE
            );

        /** Активное событие */
        $dbal
            ->addSelect('service_event.id as service_event')
            ->join(
                'service',
                ServiceEvent::class,
                'service_event',
                'service_event.main = service.id'
            );

        $dbal
            ->join(
                'service',
                ServiceInvariable::class,
                'service_invariable',
                '
                        service_invariable.main = service.id
                        AND
                        service_invariable.profile = :profile'
            )
            ->setParameter(
                key: 'profile',
                value: ($this->profile instanceof UserProfileUid) ? $this->profile : $this->UserProfileTokenStorage->getProfile(),
                type: UserProfileUid::TYPE,
            );


        /* Period */
        $dbal
            ->addSelect('period.id')
            ->addSelect('period.frm as time_from')
            ->addSelect('period.upto as time_to')
            ->join(
                'service_event',
                ServicePeriod::class,
                'period',
                'period.event = service_event.id'
            );


        /* Price */
        $dbal
            ->addSelect('price.price as service_price')
            ->addSelect('price.currency as service_currency')
            ->leftJoin('service_event',
                ServicePrice::class,
                'price',
                'service_event.id = price.event'
            );


        /* OrderService */
        $dbal
            ->leftJoin(
                'period',
                OrderService::class,
                'order_service',
                'order_service.period = period.id'
            );


        /* Order */
        $dbal
            ->leftJoin(
                'order_service',
                Order::class,
                'ord',
                'ord.event = order_service.event OR ord.event IS NULL'
            );


        /* Отобразить информацию о резервах для всех заказов, кроме статуса canceled */
        $dbal
            ->leftJoin(
                'ord',
                OrderEvent::class,
                'order_event',
                'ord.event = order_event.id AND order_event.status <> :status'
            )
            ->setParameter(
                'status',
                OrderStatus\Collection\OrderStatusCanceled::class,
                OrderStatus::TYPE
            );


        /** Агрегация данных по резервам */
        $dbal->addSelect(
            "
             CASE
                 WHEN order_service.event IS NOT NULL AND ord.event = order_service.event AND order_event.status IS NOT NULL
                 THEN
                     JSONB_BUILD_OBJECT (
                        'order_service_date', order_service.date,
                        'order_service_event', order_service.event
                     )
                 ELSE NULL
             END AS order_services"
        );


        $dbal->allGroupByExclude();

        $dbal->orderBy('period.id', 'ASC');

        $result = $this->prepareOrderService($dbal->fetchAllAssociative());

        foreach($result as $serv)
        {
            yield new ServicePeriodResult(...$serv);
        }

    }
}