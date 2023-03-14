<?php

namespace App\Component;

use App\Document\Inbox;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('create_inbox')]
final class CreateInboxComponent
{
    public function __construct(private readonly FormFactoryInterface $formFactory)
    {
    }

    public function getForm(): FormView
    {
        $inbox = new Inbox();

        $form = $this->formFactory
            ->createBuilder(FormType::class, $inbox)
            ->add('name')
            ->getForm();

        return $form->createView();
    }
}
