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

namespace BaksDev\Services\Repository\AllServices;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Services\Entity\Event\Info\ServiceInfo;
use BaksDev\Services\Entity\Event\Invariable\ServiceInvariable;
use BaksDev\Services\Entity\Event\Price\ServicePrice;
use BaksDev\Services\Entity\Service;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

class AllServicesRepository implements AllServicesInterface
{
    private bool $admin = false;

    private UserProfileUid|false $profile = false;

    private SearchDTO|false $search = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
        private readonly UserProfileTokenStorageInterface $UserProfileTokenStorage,
    ) {}

    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function forAdmin(): self
    {
        $this->admin = true;
        return $this;
    }

    /** Фильтр по профилю */
    public function byProfile(UserProfileUid $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

    public function findPaginator(): PaginatorInterface
    {

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->addSelect('service.event as id')
            ->from(Service::class, 'service');

        if(true === $this->admin)
        {
            $dbal
                ->join(
                    'service',
                    ServiceInvariable::class,
                    'invariable',
                    '
                    invariable.main = service.id'
                );
        }
        else
        {
            $dbal
                ->join(
                    'service',
                    ServiceInvariable::class,
                    'invariable',
                    '
                        invariable.main = service.id
                        AND invariable.profile = :profile'
                )
                ->setParameter(
                    key: 'profile',
                    value: ($this->profile instanceof UserProfileUid) ? $this->profile : $this->UserProfileTokenStorage->getProfile(),
                    type: UserProfileUid::TYPE,
                );
        }

        $dbal
            ->addSelect('info.name')
            ->addSelect('info.preview')
            ->join(
                'service',
                ServiceInfo::class,
                'info',
                'info.event = service.event'
            );

        $dbal
            ->addSelect('price.price')
            ->join(
                'service',
                ServicePrice::class,
                'price',
                'price.event = service.event'
            );

        if($this->search && $this->search->getQuery())
        {
            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchLike('info.preview')
                ->addSearchLike('info.name');
        }

        $dbal->orderBy('info.name');

        return $this->paginator->fetchAllHydrate($dbal, AllServicesResult::class);
    }
}