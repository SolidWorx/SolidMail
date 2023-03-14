<?php

namespace App\Controller;

use App\Document\Inbox;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/create/inbox', name: 'app_create_inbox')]
class CreateInboxController extends AbstractController
{
    public function __construct(private readonly DocumentManager $dm)
    {
    }

    public function __invoke(Request $request): Response
    {
        $inbox = new Inbox();

        $form = $this->createFormBuilder($inbox)
            ->add('name')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $inbox = $form->getData();
            assert($inbox instanceof Inbox);

            $user = $this->getUser();
            assert($user instanceof User);

            $user->addInbox($inbox);

            $this->dm->persist($inbox);
            try {
                $this->dm->flush();
            } catch (MongoDBException $e) {
                // @TODO: Log error

                $this->addFlash('error', 'An error occurred while creating the inbox.');

                return $this->redirectToRoute('app_list_inboxes');
            }

            return $this->redirectToRoute('app_list_emails', ['id' => $inbox->getId()]);
        }

        throw $this->createNotFoundException();
    }
}
