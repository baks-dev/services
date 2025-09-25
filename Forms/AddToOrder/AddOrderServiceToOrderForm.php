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

declare(strict_types=1);

namespace BaksDev\Services\Forms\AddToOrder;

use BaksDev\Orders\Order\Entity\Services\OrderService;
use BaksDev\Orders\Order\Repository\Services\AllServicePeriodByDate\AllServicePeriodByDateInterface;
use BaksDev\Orders\Order\Repository\Services\AllServicePeriodByDate\AllServicePeriodByDateResult;
use BaksDev\Orders\Order\Repository\Services\OneServiceById\OneServiceByIdInterface;
use BaksDev\Orders\Order\Type\OrderService\Period\ServicePeriodUid;
use BaksDev\Orders\Order\Type\OrderService\Service\ServiceUid;
use BaksDev\Services\Repository\AllServicesByProfile\AllServicesByProfileInterface;
use DateTimeImmutable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @see OrderService */
final class AddOrderServiceToOrderForm extends AbstractType
{
    public function __construct(
        private readonly AllServicesByProfileInterface $allServicesByProfile,
        private readonly AllServicePeriodByDateInterface $allServicePeriodByDateRepository,
        private readonly OneServiceByIdInterface $oneServiceRepository,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $allServicesByProfile = $this->allServicesByProfile->findAll();

        $builder
            ->add('serv', ChoiceType::class, [
                'choices' => $allServicesByProfile,
                'choice_value' => function(?ServiceUid $serviceInfo) {
                    return $serviceInfo?->getValue();
                },
                'choice_label' => function(?ServiceUid $serviceInfo) {
                    return $serviceInfo?->getParams();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ]);

        $builder->add('price', HiddenType::class);
        $builder->add('period', HiddenType::class);
        $builder->add('date', HiddenType::class);
        $builder->add('name', HiddenType::class);

        /** События формы */

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function(FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                /** Если выбран сервис, добавляем поля */
                if(false === empty($data['serv']))
                {
                    $service = $this->oneServiceRepository->find(new ServiceUid($data['serv']));

                    /** Name */
                    $form->add('name', HiddenType::class, ['empty_data' => $service->getName()]);

                    /** Price */
                    $price = $service->getPrice();

                    $form->add('price', MoneyType::class, [
                            'attr' => [
                                'data-min' => $price->getValue()
                            ],
                            'empty_data' => (string) $price->getValue(),
                            'currency' => $service->getCurrency(),
                            'label' => 'Цена',
                            'scale' => 0,
                            'required' => true,
                        ]
                    );

                    /** Date */

                    $form->add('date', DateType::class, [
                        'widget' => 'single_text',
                        'html5' => false,
                        'label' => 'Дата',
                        'format' => 'dd.MM.yyyy',
                        'input' => 'datetime_immutable',
                        'attr' => ['class' => 'js-datepicker'],
                        'required' => true,
                    ]);

                    /** Если выбрана дата, добавляем поле */
                    if(false === empty($data['date']))
                    {
                        $periods = $this->allServicePeriodByDateRepository
                            ->byDate(new DateTimeImmutable($data['date']))
                            ->findAll(new ServiceUid($data['serv']));

                        $periodResults = iterator_to_array($periods);

                        $periodChoice = array_map(function(AllServicePeriodByDateResult $period) {

                            $data = [
                                'time' => $period->getFrom()->format('H:i').' - '.$period->getTo()->format('H:i'),
                                'active' => $period->isOrderServiceActive()
                            ];

                            return new ServicePeriodUid($period->getPeriodId(), $data);
                        }, $periodResults);

                        $form->add('period', ChoiceType::class, [
                            'choices' => $periodChoice,
                            'choice_value' => function(?ServicePeriodUid $period) {

                                return $period?->getValue().'_'.$period?->getParams('time');
                            },
                            'choice_label' => function(ServicePeriodUid $period) {
                                return $period->getParams('time').($period->getParams('active') === true ? ' - забронировано' : '');
                            },
                            'choice_attr' => function($choice) {
                                return ($choice->getParams('active') === true)
                                    ? ['disabled' => 'disabled']
                                    : [];
                            },
                            'placeholder' => 'Выберите период',
                            'label' => 'Период',
                            'expanded' => false,
                            'multiple' => false,
                            'required' => true,
                        ]);

                        $form->add(
                            'order_service_add',
                            ButtonType::class,
                            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
                        );
                    }

                }
            }
        );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AddOrderServiceToOrderDTO::class,
        ]);
    }
}