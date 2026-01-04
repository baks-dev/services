<?php

namespace BaksDev\Services\Repository\ServicePeriods\Tests;

use BaksDev\Orders\Order\Type\OrderService\Service\ServiceUid;
use BaksDev\Services\Repository\ServicePeriods\ServicePeriodResult;
use BaksDev\Services\Repository\ServicePeriods\ServicePeriodsInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
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

        $results = $ServicePeriodsInterface
            ->onProfile(new UserProfileUid($profile))
            ->findAll(new ServiceUid(ServiceUid::TEST));

        if(false === $results || false === $results->valid())
        {
            return;
        }

        foreach($results as $ServicePeriodResult)
        {
            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(ServicePeriodResult::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

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
}