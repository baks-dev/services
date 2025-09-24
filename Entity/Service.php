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

namespace BaksDev\Services\Entity;

use BaksDev\Orders\Order\Type\OrderService\Service\ServiceUid;
use BaksDev\Services\Entity\Event\ServiceEvent;
use BaksDev\Services\Type\Event\ServiceEventUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/* Services */

#[ORM\Entity]
#[ORM\Table(name: 'service')]
class Service
{
    /**
     * Идентификатор сущности
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ServiceUid::TYPE)]
    private ServiceUid $id;

    /**
     * Идентификатор события
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ServiceEventUid::TYPE, unique: true)]
    private ServiceEventUid $event;

    public function __construct()
    {
        $this->id = new ServiceUid();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    /**
     * Идентификатор
     */
    public function getId(): ServiceUid
    {
        return $this->id;
    }

    /**
     * Идентификатор события
     */
    public function getEvent(): ServiceEventUid
    {
        return $this->event;
    }

    public function setEvent(ServiceEventUid|ServiceEvent $event): void
    {
        $this->event = $event instanceof ServiceEvent ? $event->getId() : $event;
    }
}