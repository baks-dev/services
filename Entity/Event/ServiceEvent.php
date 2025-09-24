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

namespace BaksDev\Services\Entity\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Orders\Order\Type\OrderService\Service\ServiceUid;
use BaksDev\Services\Entity\Event\Info\ServiceInfo;
use BaksDev\Services\Entity\Event\Invariable\ServiceInvariable;
use BaksDev\Services\Entity\Event\Modify\ServiceModify;
use BaksDev\Services\Entity\Event\Period\ServicePeriod;
use BaksDev\Services\Entity\Event\Price\ServicePrice;
use BaksDev\Services\Entity\Service;
use BaksDev\Services\Type\Event\ServiceEventUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'service_event')]
class ServiceEvent extends EntityEvent
{
    /**
     * Идентификатор События
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ServiceEventUid::TYPE)]
    private ServiceEventUid $id;

    /**
     * Идентификатор Service
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ServiceUid::TYPE, nullable: false)]
    private ?ServiceUid $main = null;

    /**
     * Цена От
     */
    #[ORM\OneToOne(targetEntity: ServicePrice::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ServicePrice $price;

    /**
     * Info - name и preview
     */
    #[ORM\OneToOne(targetEntity: ServiceInfo::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ServiceInfo $info;

    /**
     * Модификатор
     */
    #[ORM\OneToOne(targetEntity: ServiceModify::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ServiceModify $modify;

    /**
     * Period
     */
    #[ORM\OneToMany(targetEntity: ServicePeriod::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $period;


    /**
     * Постоянная величина
     */
    #[ORM\OneToOne(targetEntity: ServiceInvariable::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ServiceInvariable $invariable;

    public function __construct()
    {
        $this->id = new ServiceEventUid();
        $this->modify = new ServiceModify($this);
    }

    /**
     * Идентификатор события
     */

    public function __clone()
    {
        $this->id = clone new ServiceEventUid();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): ServiceEventUid
    {
        return $this->id;
    }

    /**
     * Идентификатор Service
     */
    public function setMain(ServiceUid|Service $main): void
    {
        $this->main = $main instanceof Service ? $main->getId() : $main;
    }

    public function getMain(): ?ServiceUid
    {
        return $this->main;
    }

    public function getDto($dto): mixed
    {
        if($dto instanceof ServiceEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof ServiceEventInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function getInfo(): ServiceInfo
    {
        return $this->info;
    }
}