<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Services\Forms\AddToOrder;

use BaksDev\Orders\Order\Type\OrderService\Period\ServicePeriodUid;
use BaksDev\Orders\Order\Type\OrderService\Service\ServiceUid;
use BaksDev\Orders\Order\UseCase\Admin\Edit\Service\Price\OrderServicePriceDTO;
use BaksDev\Reference\Money\Type\Money;
use DateTimeImmutable;
use Generator;
use Symfony\Component\Validator\Constraints as Assert;

/** @see OrderService */
final class AddOrderServiceToOrderDTO
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public ?ServiceUid $serv = null;

    #[Assert\NotBlank]
    private ?Money $price = null;

    #[Assert\NotBlank]
    private DateTimeImmutable|string|null $date = null;

    #[Assert\NotBlank]
    private ServicePeriodUid|string|null $period = null;

    #[Assert\Valid]
    public OrderServicePriceDTO $priceDTO;

    public ?string $name = null;

    public ?string $time = null;

    public array|false $allServices = false;

    public function __construct()
    {
        $this->priceDTO = new OrderServicePriceDTO();
    }

    public function setServ(ServiceUid|string|null $serv): self
    {
        $this->serv = $serv instanceof ServiceUid ? $serv : new ServiceUid($serv);
        return $this;
    }

    public function getServ(): ?ServiceUid
    {
        return $this->serv;
    }

    public function setPrice(Money|float|null $price): self
    {
        $this->price = $price instanceof Money ? $price : new Money($price);
        $this->priceDTO->setPrice($this->price);
        return $this;
    }

    public function getPrice(): ?Money
    {
        return $this->price;
    }

    public function setPeriod(ServicePeriodUid|string|null $period): self
    {
        $this->period = $period;
        return $this;
    }

    public function getPeriod(): ?ServicePeriodUid
    {
        return $this->period;
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable|string|null $date): void
    {
        $this->date = $date;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(?string $time): void
    {
        $this->time = $time;
    }

    public function getAllServices(): array|false
    {
        return $this->allServices;
    }

    public function setAllServices(false|Generator $services): self
    {
        if(false !== $services)
        {
            $this->allServices = iterator_to_array($services);
        }

        return $this;
    }
}