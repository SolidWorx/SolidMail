<?php

namespace App\Controller;

use App\Document\User;
use App\Form\RegistrationType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/register', name: 'app_register')]
class RegisterController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $dm,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {

    }

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(RegistrationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));

            $this->dm->persist($user);

            $this->dm->flush();

            return $this->redirectToRoute('app_list_inboxes');
        }

        return $this->render('register/index.html.twig', [
            'form' => $form
        ]);
    }
}
