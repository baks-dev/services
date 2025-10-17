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

namespace BaksDev\Services\Repository\ServicePeriods;

use BaksDev\Orders\Order\Type\OrderService\Service\ServiceUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Services\Type\Event\ServiceEventUid;
use BaksDev\Orders\Order\Type\OrderService\Period\ServicePeriodUid;
use DateTimeImmutable;

final class ServicePeriodResult
{

    public function __construct(
        private string $id,
        private string $service_id,
        private string $time_from,
        private string $time_to,

        private ?string $service_event,

        private ?string $order_services,

        private int|null $service_price,
        private string|null $service_currency,

        private array $order_services_data,


    ) {}

    public function getOrderServicesData(): array
    {
        return $this->order_services_data;
    }


    public function getOrderServices(): ?array
    {

        if(is_null($this->order_services))
        {
            return null;
        }

        if(false === json_validate($this->order_services))
        {
            return null;
        }

        $order_services = json_decode($this->order_services, null, 512, JSON_THROW_ON_ERROR);

        if(null === current($order_services))
        {
            return null;
        }

        return $order_services;

    }


    public function getServiceId(): ServiceUid
    {
        return new ServiceUid($this->service_id);
    }

    public function getServiceEvent(): ServiceEventUid
    {
        return new ServiceEventUid($this->service_event);
    }


    public function getPeriodId(): ServicePeriodUid
    {
        return new ServicePeriodUid($this->id);
    }

    public function getTimeFrom(): ?DateTimeImmutable
    {
        return new DateTimeImmutable($this->time_from);
    }

    public function getTimeTo(): ?DateTimeImmutable
    {
        return new DateTimeImmutable($this->time_to);
    }


    public function getServicePrice(): Money
    {

        $price = new Money($this->service_price, true);

        return $price;
    }

    public function getServiceCurrency(): Currency|bool
    {
        return new Currency($this->service_currency);
    }
}