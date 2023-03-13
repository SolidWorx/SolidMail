<?php

namespace App\Controller;

use App\Document\Email;
use App\Document\Inbox;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/app/list/emails/{id}', name: 'app_list_emails')]
class ListEmailsController extends AbstractController
{
    public function __construct(private readonly DocumentManager $dm)
    {
    }

    public function __invoke(string $id): Response
    {
        try {
            $inbox = $this->dm->getRepository(Inbox::class)->find($id);
        } catch (LockException | MappingException $e) {
            throw new BadRequestHttpException('Unable to open inbox.', $e);
        }

        if (! $inbox instanceof Inbox) {
            throw new BadRequestHttpException('Unable to open inbox.');
        }

        dd(
            $inbox->getMessages()->toArray(),
            $this->dm->getRepository(Email::class)->findBy(['inbox' => $inbox])
        );
    }
}
