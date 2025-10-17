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

namespace BaksDev\Services\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Orders\Order\Repository\Services\OneServiceById\OneServiceByIdInterface;
use BaksDev\Services\Entity\Service;
use BaksDev\Services\Repository\ServicePeriods\ServicePeriodsInterface;
use DateInterval;
use DatePeriod;
use DateTime;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_SERVICE_EDIT')]
final class ServiceController extends AbstractController
{

    #[Route('/admin/service/{id}', name: 'admin.service', defaults: ['id' => null], methods: ['GET', 'POST'])]
    public function service(
        #[MapEntity] Service $service,
        Request $request,
        ServicePeriodsInterface $servicePeriods,
        OneServiceByIdInterface $serviceById
    )
    {

        /* Получаем список периодов по услуге */
        $periods = $servicePeriods
            ->findAll($service->getId());

        $periods = iterator_to_array($periods);

        /* Добавить "пустое значение" */
        array_unshift($periods, null);

        /* Даты от текущего дня, до $days_count */
        $dates = function($date = 'now', $format = 'm-d, D', $days_count = 11) {

            $dates = []; /* Инициализация пустого массива для хранения дат */
            $interval = DateInterval::createFromDateString('1 day'); /* Интервал в 1 день */
            $period = new DatePeriod(new DateTime($date), $interval, $days_count); /* Период в $days_count дней (включая начальную дату) */

            /* Перебор всех дат в периоде, добавление даты в массив */
            foreach($period as $dateObj)
            {
                $dates[] = $dateObj;
            }

            return $dates;
        };

        $serviceData = $serviceById->find($service->getId());

        return $this->render(
            [
                'periods' => $periods,
                'dates' => $dates(),
                'name' => $serviceData->getName(),
            ]
        );
    }

}