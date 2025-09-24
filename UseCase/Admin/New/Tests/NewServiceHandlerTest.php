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

namespace BaksDev\Services\UseCase\Admin\New\Tests;

use BaksDev\Services\UseCase\Admin\New\Info\ServiceInfoDTO;
use BaksDev\Services\UseCase\Admin\New\Invariable\ServiceInvariableDTO;
use BaksDev\Services\UseCase\Admin\New\Price\ServicePriceDTO;
use BaksDev\Services\UseCase\Admin\New\ServiceDTO;
use BaksDev\Services\UseCase\Admin\New\ServiceHandler;
use BaksDev\Services\Entity\Event\ServiceEvent;
use BaksDev\Services\Entity\Service;
use BaksDev\Orders\Order\Type\ServiceUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\DBAL\Types\DateIntervalType;
use Doctrine\DBAL\Types\TimeType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

use BaksDev\Reference\Currency\Type\Currencies\RUR;

#[Group('services')]
#[When(env: 'test')]
class NewServiceHandlerTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $Service = $em->getRepository(Service::class)
            ->find(ServiceUid::TEST);

        if($Service)
        {
            $em->remove($Service);
        }

        $ServiceEvent = $em->getRepository(ServiceEvent::class)
            ->findBy(['main' => ServiceUid::TEST]);

        foreach($ServiceEvent as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();
    }

    public function testUseCase(): void
    {

        $ServiceDTO = new ServiceDTO();

        /**
         * Price
         */
         $ServicePriceDTO = new ServicePriceDTO();
         $ServicePriceDTO
             ->setPrice(100.00)
             ->setCurrency(RUR::CURRENCY);

        $ServiceDTO->setPrice($ServicePriceDTO);

        /**
         * Info
         */
        $ServiceInfoDTO = new ServiceInfoDTO();
        $ServiceInfoDTO->setName('test')
            ->setPreview('test preview');

        $ServiceDTO->setInfo($ServiceInfoDTO);

        /**
         * Invariable
         */
        $ServiceInvariableDTO = new ServiceInvariableDTO();
        $ServiceInvariableDTO->setProfile(new UserProfileUid(UserProfileUid::TEST));

        $ServiceDTO->setInvariable($ServiceInvariableDTO);

//        dd($ServiceDTO);

        self::bootKernel();

        /** @var ServiceHandler $ServiceHandler */
        $ServiceHandler = self::getContainer()->get(ServiceHandler::class);
        $handle = $ServiceHandler->handle($ServiceDTO);

        self::assertTrue(($handle instanceof Service), $handle.': Ошибка Order');
    }
}
