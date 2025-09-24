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
use BaksDev\Services\Entity\Service;
use BaksDev\Services\UseCase\Admin\New\Invariable\ServiceInvariableDTO;
use BaksDev\Services\UseCase\Admin\New\Period\ServicePeriodDTO;
use BaksDev\Services\UseCase\Admin\New\ServiceDTO;
use BaksDev\Services\UseCase\Admin\New\ServiceForm;
use BaksDev\Services\UseCase\Admin\New\ServiceHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_SERVICE_NEW')]
final class NewController extends AbstractController
{
    #[Route('/admin/service/new/{id}', name: 'admin.newedit.new', defaults: ['id' => null], methods: ['GET', 'POST'])]
    public function news(
        Request $request,
        ServiceHandler $serviceHandler,
    ): Response
    {

        $ServiceDTO = new ServiceDTO();

        $ServicePeriodDTO = new ServicePeriodDTO();
        $ServiceDTO->addPeriod($ServicePeriodDTO);


        /* Заполнить профиль */
        $ServiceInvariableDTO = new ServiceInvariableDTO();
        $ServiceInvariableDTO->setProfile($this->getProfileUid());

        $ServiceDTO->setInvariable($ServiceInvariableDTO);


        /** Форма */
        $form = $this
            ->createForm(
                type: ServiceForm::class,
                data: $ServiceDTO,
                options: ['action' => $this->generateUrl('services:admin.newedit.new'),]
            )
            ->handleRequest($request);


        if($form->isSubmitted() && $form->isValid() && $form->has('service'))
        {

            $this->refreshTokenForm($form);

            $handle = $serviceHandler->handle($ServiceDTO);

            $this->addFlash
            (
                'page.new',
                $handle instanceof Service ? 'success.new' : 'danger.new',
                'service.admin',
                $handle
            );

            return $handle instanceof Service ? $this->redirectToRoute('services:admin.index') : $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);

    }
}