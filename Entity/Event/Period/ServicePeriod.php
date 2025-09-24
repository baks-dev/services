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

namespace BaksDev\Services\Entity\Event\Period;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Orders\Order\Type\OrderService\Period\ServicePeriodUid;
use BaksDev\Services\Entity\Event\ServiceEvent;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'service_period')]
class ServicePeriod extends EntityEvent
{
    #[ORM\Id]
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ServicePeriodUid::TYPE)]
    private ServicePeriodUid $id;

    #[ORM\ManyToOne(targetEntity: ServiceEvent::class, inversedBy: 'period')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private ServiceEvent $event;

    /** Время услуги ОТ */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $frm;

    /** Время услуги ДО */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $upto;

    public function __construct(ServiceEvent $event)
    {
        $this->id = new ServicePeriodUid();
        $this->event = $event;
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function getId(): ServicePeriodUid
    {
        return $this->id;
    }

    public function getEvent(): ServiceEvent
    {
        return $this->event;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof ServicePeriodInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof ServicePeriodInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}