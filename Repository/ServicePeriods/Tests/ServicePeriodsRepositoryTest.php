<?php

namespace BaksDev\Services\Repository\ServicePeriods\Tests;

use BaksDev\Services\Repository\ServicePeriods\ServicePeriodResult;
use BaksDev\Services\Repository\ServicePeriods\ServicePeriodsInterface;
use BaksDev\Services\Type\Event\ServiceEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
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

        $profile = $_SERVER['TEST_PROFILE'] ?? UserProfileUid::TEST;
        $result = $ServicePeriodsInterface
            ->onProfile(new UserProfileUid($profile))
            ->findAll(new ServiceEventUid('01992ea7-fc74-767c-8d39-d936162b3632'));

        if(false === $result)
        {
            return;
        }

        $ServicePeriodResult = $result->current();

        // Вызываем все геттеры
        $reflectionClass = new \ReflectionClass(ServicePeriodResult::class);
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach($methods as $method)
        {
            // Методы без аргументов
            if($method->getNumberOfParameters() === 0)
            {
                // Вызываем метод
                $data = $method->invoke($ServicePeriodResult);
            }
        }
    }
}