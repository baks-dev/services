<?php

declare(strict_types=1);

namespace BaksDev\Services\Controller\Admin\OrderService;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Services\Forms\AddToOrder\AddOrderServiceToOrderDTO;
use BaksDev\Services\Forms\AddToOrder\AddOrderServiceToOrderForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_SERVICE_INDEX')]
final class AddToEditOrderController extends AbstractController
{
    #[Route('/admin/order/service/edit', name: 'admin.order.service.edit', methods: ['GET', 'POST',])]
    public function edit(
        Request $request
    ): Response
    {
        $addForm = $this
            ->createForm(
                type: AddOrderServiceToOrderForm::class,
                data: new AddOrderServiceToOrderDTO(),
                options: ['action' => $this->generateUrl('services:admin.order.service.edit')]
            );

        $addForm->handleRequest($request);

        if($addForm->isSubmitted() && $addForm->isValid())
        {
            $this->refreshTokenForm($addForm);

            return new JsonResponse(['valid' => true]);
        }

        return $this->render(['form' => $addForm->createView()]);
    }
}