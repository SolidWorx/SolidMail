<?php

namespace App\Controller;

use App\Document\Email;
use App\Document\Inbox;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use PhpMimeMailParser\Parser;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/app/list/emails/{id}', name: 'app_list_emails')]
class ListEmailsController extends AbstractController
{
    public function __construct(private readonly DocumentManager $dm)
    {
    }

    #[Template('messages/list.html.twig')]
    public function __invoke(string $id): array
    {
        // @TODO: Ensure the user has access to the inbox

        try {
            $inbox = $this->dm->getRepository(Inbox::class)->find($id);
        } catch (LockException | MappingException $e) {
            throw new BadRequestHttpException('Unable to open inbox.', $e);
        }

        if (! $inbox instanceof Inbox) {
            throw new BadRequestHttpException('Unable to open inbox.');
        }

        return [
            'inbox' => $inbox,
            'messages' => array_map(
                static function (Email $email) {
                    $parser = new Parser();
                    $parser->setText($email->getMessage());

                    return [
                        'from' => $parser->getHeader('from'),
                        'to' => $parser->getHeader('to'),
                        'subject' => $parser->getHeader('subject'),
                        'hasAttachments' => count($parser->getAttachments()) > 0,
                        'date' => $parser->getHeader('date'),
                    ];
                },
                $inbox->getMessages()->slice(0, 20)
            ),
        ];
    }
}
