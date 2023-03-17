<?php

namespace App\Controller;

use App\Document\User;
use App\Form\RegistrationType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;

#[Route('/register', name: 'app_register')]
class RegisterController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $dm,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserAuthenticatorInterface $userAuthenticator,
        private readonly AuthenticatorInterface $authenticator,
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

            try {
                $this->dm->flush();
            } catch (MongoDBException $e) {
                $this->addFlash('error', 'An error occurred while trying to register your account. Please try again later.');

                return $this->redirectToRoute('app_register');
            }

            return $this->userAuthenticator->authenticateUser($user, $this->authenticator, $request);
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
