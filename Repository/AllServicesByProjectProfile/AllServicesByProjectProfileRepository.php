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
 *
 */

declare(strict_types=1);

namespace BaksDev\Services\Repository\AllServicesByProjectProfile;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Orders\Order\Type\OrderService\Service\ServiceUid;
use BaksDev\Services\Entity\Event\Info\ServiceInfo;
use BaksDev\Services\Entity\Event\Invariable\ServiceInvariable;
use BaksDev\Services\Entity\Service;
use Generator;
use InvalidArgumentException;

final readonly class AllServicesByProjectProfileRepository implements AllServicesByProjectProfileInterface
{
    public function __construct(
        private DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    /**
     * Возвращает массив идентификаторов услуг с доп. информацией
     *
     * @return Generator{int, ServiceUid}|false
     */
    public function findAll(): Generator|false
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        if(false === $dbal->isProjectProfile())
        {
            throw new InvalidArgumentException('Не установлен PROJECT_PROFILE');
        }

        $dbal
            ->addSelect('service.id AS service_id')
            ->from(Service::class, 'service');

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
            ->addSelect('service_info.name')
            ->join(
                'service_invariable',
                ServiceInfo::class,
                'service_info',
                'service_info.event = service_invariable.event'
            );

        $result = $dbal->fetchAllAssociative();

        if(true === empty($result))
        {
            return false;
        }

        foreach($result as $item)
        {
            yield new ServiceUid($item['service_id'], $item['name']);
        }
    }

}