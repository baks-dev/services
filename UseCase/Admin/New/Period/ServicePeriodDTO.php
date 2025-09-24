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
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING frm,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Services\UseCase\Admin\New\Period;

use BaksDev\Services\Entity\Event\Period\ServicePeriodInterface;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;


final class ServicePeriodDTO implements ServicePeriodInterface
{

    /**
     * Время ОТ
     */
    #[Assert\NotBlank]
    private ?DateTimeImmutable $frm = null;

    /**
     * Время ДО
     */
    #[Assert\NotBlank]
    private ?DateTimeImmutable $upto = null;

    public function getFrm(): ?DateTimeImmutable
    {
        return $this->frm;
    }

    public function setFrm(DateTimeImmutable|int|string|null $frm): self
    {
        $this->frm = is_int($frm) || is_string($frm) ? new DateTimeImmutable($frm) : $frm;
        return $this;
    }


    public function getUpto(): ?DateTimeImmutable
    {
        return $this->upto;
    }

    public function setUpto(DateTimeImmutable|int|string|null $upto): self
    {
        $this->upto = is_int($upto) || is_string($upto) ? new DateTimeImmutable($upto) : $upto;

        return $this;
    }

}