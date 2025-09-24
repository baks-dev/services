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
use BaksDev\Orders\Order\Entity\Order;
use BaksDev\Orders\Order\Entity\Services\OrderService;
use BaksDev\Services\Entity\Event\Invariable\ServiceInvariable;
use BaksDev\Services\Entity\Event\Period\ServicePeriod;
use BaksDev\Services\Entity\Event\Price\ServicePrice;
use BaksDev\Services\Entity\Event\ServiceEvent;
use BaksDev\Services\Entity\Service;
use BaksDev\Services\Type\Event\ServiceEventUid;
use Generator;
use InvalidArgumentException;

final class ServicePeriodsRepository implements ServicePeriodsInterface
{
    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function findAll(ServiceEventUid $serviceEventUid): false|Generator
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        if(false === $dbal->isProjectProfile())
        {
            throw new InvalidArgumentException('Не установлен PROJECT_PROFILE');
        }

        $dbal->from(ServicePeriod::class, 'period')
            ->addSelect('period.id')
            ->addSelect('period.frm as time_from')
            ->addSelect('period.upto as time_to');

        $dbal->where('period.event = :serviceEventUid')
            ->setParameter('serviceEventUid', $serviceEventUid);

        $dbal
            ->addSelect('service.id as service_id')
            ->leftJoin(
                'period',
                Service::class,
                'service',
                'period.event = service.event'
            );
        $dbal
            ->join(
                'service',
                ServiceInvariable::class,
                'service_invariable',
                '
                        service_invariable.main = service.id
                        AND
                        service_invariable.profile = :'.$dbal::PROJECT_PROFILE_KEY
            );


        $dbal
            ->addSelect('service_event.id as service_event')
            ->addSelect('service_event.main as service_id')
            ->leftJoin(
                'period',
                ServiceEvent::class,
                'service_event',
                'period.event = service_event.id'
            );

        $dbal
            ->addSelect('price.price as service_price')
            ->addSelect('price.currency as service_currency')
            ->leftJoin('service_event',
                ServicePrice::class,
                'price',
                'service_event.id = price.event'
            );


        $dbal
            ->leftJoin(
                'period',
                OrderService::class,
                'order_service',
                'period.id = order_service.period '
            );


        $dbal
            ->leftJoin(
                'order_service',
                Order::class,
                'ord',
                'ord.event = order_service.event OR ord.event IS NULL'
            );


        /** Агрегация данных по резервам */
        $dbal->addSelect(
            "
            JSON_AGG( DISTINCT
                 CASE
                     WHEN order_service.event IS NOT NULL AND ord.event = order_service.event
                     THEN
                         JSONB_BUILD_OBJECT (
                            'order_service_date', order_service.date,
                            'order_service_event', order_service.event
                         )
                     ELSE NULL
                END
            ) AS order_services"
        );


        $dbal->allGroupByExclude();


        $dbal->orderBy('period.id', 'ASC');

        $result = $dbal
            // ->enableCache('services', 3600)
            ->fetchAllHydrate(ServicePeriodResult::class);

        return ($result->valid() === true) ? $result : false;
    }
}