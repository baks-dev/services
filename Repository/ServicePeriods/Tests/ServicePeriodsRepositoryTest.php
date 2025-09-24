<?php

namespace BaksDev\Services\Repository\ServicePeriods\Tests;

use BaksDev\Services\Repository\ServicePeriods\ServicePeriodsInterface;
use BaksDev\Services\Type\Event\ServiceEventUid;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('services')]
#[When(env: 'test')]
class ServicePeriodsRepositoryTest extends KernelTestCase
{
    public function testRepository(): void
    {
        self::assertTrue(true);

        /** @var ServicePeriodsInterface $ServicePeriodsInterface */
        $ServicePeriodsInterface = self::getContainer()->get(ServicePeriodsInterface::class);

        $result = $ServicePeriodsInterface
            ->findAll(new ServiceEventUid('0199138b-d2ee-74f4-af25-4a100169d1ab'));
        //            ->forOrderEvent(new OrderEventUid('019248a1-2b0a-7609-ada9-ba58aaf76e5d'))
        //            ->findAll();

        dd($result);

    }
}