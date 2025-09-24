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

namespace BaksDev\Services\UseCase\Admin\New;

use BaksDev\Services\Entity\Event\ServiceEventInterface;
use BaksDev\Services\Type\Event\ServiceEventUid;
use BaksDev\Services\UseCase\Admin\New\Info\ServiceInfoDTO;
use BaksDev\Services\UseCase\Admin\New\Invariable\ServiceInvariableDTO;
use BaksDev\Services\UseCase\Admin\New\Period\ServicePeriodDTO;
use BaksDev\Services\UseCase\Admin\New\Price\ServicePriceDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ServiceEvent */
final class ServiceDTO implements ServiceEventInterface
{

    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?ServiceEventUid $id = null;

    #[Assert\NotBlank]
    private ServiceInfoDTO $info;

    #[Assert\NotBlank]
    private ServiceInvariableDTO $invariable;

    #[Assert\NotBlank]
    private ServicePriceDTO $price;


    /** Коллекция диапазонов в услуге */
    #[Assert\Valid]
    #[Assert\NotBlank]
    private ArrayCollection $period;


    public function __construct()
    {
        $this->period = new ArrayCollection();
    }


    /**
     * Идентификатор события
     */
    public function getEvent(): ?ServiceEventUid
    {
        return $this->id;
    }

    public function getInfo(): ServiceInfoDTO
    {
        return $this->info;
    }

    public function setInfo(ServiceInfoDTO $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getInvariable(): ServiceInvariableDTO
    {
        return $this->invariable;
    }

    public function setInvariable(ServiceInvariableDTO $invariable): self
    {
        $this->invariable = $invariable;

        return $this;
    }

    public function getPrice(): ServicePriceDTO
    {
        return $this->price;
    }

    public function setPrice(ServicePriceDTO $price): self
    {
        $this->price = $price;

        return $this;
    }


    /**
     * Коллекция диапазонов в услуге
     *
     * @return ArrayCollection<int, ServicePeriodDTO>
     */
    public function getPeriod(): ArrayCollection
    {
        return $this->period;
    }

    public function setPeriod(ArrayCollection $period): void
    {
        $this->period = $period;
    }

    public function addPeriod(ServicePeriodDTO $period): void
    {
        if(!$this->period->contains($period))
        {
            $this->period->add($period);
        }
    }

    public function removePeriod(ServicePeriodDTO $period): void
    {
        $this->period->removeElement($period);
    }


}