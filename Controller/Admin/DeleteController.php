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
use BaksDev\Services\Entity\Event\ServiceEvent;
use BaksDev\Services\Entity\Service;
use BaksDev\Services\UseCase\Admin\Delete\ServiceDeleteDTO;
use BaksDev\Services\UseCase\Admin\Delete\ServiceDeleteForm;
use BaksDev\Services\UseCase\Admin\Delete\ServiceDeleteHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_SERVICE_DELETE')]
class DeleteController extends AbstractController
{
    #[Route('/admin/service/delete/{id}', name: 'admin.delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity] ServiceEvent $ServiceEvent,
        ServiceDeleteHandler $ServiceDeleteHandler,
    ): Response
    {
        $ServiceDeleteDTO = new ServiceDeleteDTO();
        $ServiceEvent->getDto($ServiceDeleteDTO);

        $form = $this->createForm(ServiceDeleteForm::class, $ServiceDeleteDTO, [
            'action' => $this->generateUrl('services:admin.delete', ['id' => $ServiceDeleteDTO->getEvent()]),
        ]);

        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid() && $form->has('delete'))
        {
            $this->refreshTokenForm($form);

            $handle = $ServiceDeleteHandler->handle($ServiceDeleteDTO);

            $this->addFlash(
                'page.delete',
                $handle instanceof Service ? 'success.delete' : 'danger.delete',
                'service.admin',
                $handle
            );

            return $this->redirectToRoute('services:admin.index');
        }

        return $this->render(
            [
                'form' => $form->createView(),
                'name' => $ServiceEvent->getInfo()->getName(),
            ]
        );
    }
}