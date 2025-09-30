<?php

declare(strict_types=1);

namespace BaksDev\Services\Controller\Admin\OrderService;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Services\Forms\AddToOrder\AddOrderServiceToOrderDTO;
use BaksDev\Services\Forms\AddToOrder\AddOrderServiceToOrderForm;
use BaksDev\Services\Repository\AllServicesByProfile\AllServicesByProfileInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_SERVICE_INDEX')]
final class AddToNewOrderController extends AbstractController
{
    #[Route('/admin/order/service/add/new', name: 'admin.order.service.new', methods: ['GET', 'POST',])]
    public function edit(
        Request $request,
        AllServicesByProfileInterface $allServicesByProfile,
    ): Response
    {
        $allServicesByProfile = $allServicesByProfile->findAll();

        $AddOrderServiceToOrderDTO = new AddOrderServiceToOrderDTO();
        $AddOrderServiceToOrderDTO->setAllServices($allServicesByProfile);

        $addForm = $this
            ->createForm(
                type: AddOrderServiceToOrderForm::class,
                data: $AddOrderServiceToOrderDTO,
                options: ['action' => $this->generateUrl('services:admin.order.service.new')]
            );

        $addForm->handleRequest($request);

        return $this->render(['form' => $addForm->createView()]);
    }
}