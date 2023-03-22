<?php

namespace App\Controller;

use App\Document\Email;
use App\Document\Inbox;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use PhpMimeMailParser\Parser;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use function array_diff;
use function array_filter;
use function array_map;
use function array_merge;
use function substr;

#[Route('/app/inbox/{inboxId}/message/view/{messageId}', name: 'app_view_message')]
class ViewMessageController extends AbstractController
{
    public function __construct(private readonly DocumentManager $documentManager)
    {
    }

    #[Template('messages/view.html.twig')]
    public function __invoke(string $inboxId, string $messageId): array|Response
    {
        /** @var Inbox $inbox */
        $inbox = $this->documentManager->find(Inbox::class, $inboxId);

        /** @var Email $message */
        $message = $this->documentManager->find(Email::class, $messageId);

        // @TODO: Check if user has access to the inbox/message

        if (!$inbox->getMessages()->contains($message)) {
            throw $this->createNotFoundException();
        }

        if ($message->isUnread()) {
            $message->setUnread(false);
            try {
                $this->documentManager->flush();
            } catch (MongoDBException) {
                // @TODO: Log error
            }
        }

        $parser = new Parser();
        $parser->setText($message->getMessage());

        $to = explode(',', $parser->getHeader('to'));
        $cc = explode(',', $parser->getHeader('cc'));

        $addresses = array_map(
            static fn (Address $address) => $address->getAddress(),
            array_map(
                Address::create(...),
                array_map(
                    trim(...),
                    array_filter(
                        array_merge($to, $cc)
                    )
                )
            )
        );

        return [
            'message' => $message,
            'bcc' => array_diff(
                array_map(
                    static fn (string $address) => substr($address, 1, -1),
                    $message->getRecipients()
                ),
                $addresses,
            ),
            'parsedMessage' => $parser,
            'inbox' => $inbox,
        ];
    }
}
