<?php

namespace App\Controller;

use App\Document\Inbox;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function bin2hex;
use function dd;

#[Route('/app/list/inboxes', name: 'app_list_inboxes')]
class ListInboxesController extends AbstractController
{
    public function __construct(private readonly DocumentManager $dm)
    {

    }

    public function __invoke(): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        /*$inbox = new Inbox();
        $inbox->setName('Inbox 1');
        $inbox->setUsername(bin2hex(random_bytes(10)));
        $inbox->setPassword(bin2hex(random_bytes(10)));

        $user->addInbox($inbox);

        $this->dm->persist($inbox);
        $this->dm->persist($user);
        $this->dm->flush();*/

        $inboxes = $this->dm->getRepository(Inbox::class)->findAll();

        return new JsonResponse($user->getInboxes()->toArray());
    }
}
