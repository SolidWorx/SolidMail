<?php
declare(strict_types=1);

namespace App\Component;

use App\Document\Email;
use App\Document\Inbox;
use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\BSON\ObjectId;
use PhpMimeMailParser\Parser;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use function count;

#[AsLiveComponent('inbox_messages')]
class InboxMessages
{
    use DefaultActionTrait;

    private const DEFAULT_PER_PAGE = 15;

    public function __construct(private readonly DocumentManager $dm)
    {
    }

    #[LiveProp(writable: true)]
    public int $page = 0;

    #[LiveProp()]
    public string $inboxId;

    public function getInbox(): Inbox
    {
        return $this->dm->find(Inbox::class, new ObjectId($this->inboxId));
    }

    public function getMessages(): array
    {
        $messages = $this->dm
            ->createQueryBuilder(Email::class)
            ->field('inbox.$id')
            ->equals(new ObjectId($this->inboxId))
            ->limit(self::DEFAULT_PER_PAGE)
            ->skip($this->page * self::DEFAULT_PER_PAGE)
            ->sort('dateReceived', 'DESC')
            ->getQuery()
            ->execute();

        return array_map(
            static function (Email $email) {
                $parser = new Parser();
                $parser->setText($email->getMessage());

                return [
                    'id' => $email->getId(),
                    'from' => $parser->getHeader('from'),
                    'to' => $parser->getHeader('to'),
                    'unread' => $email->isUnread(),
                    'subject' => $parser->getHeader('subject'),
                    'hasAttachments' => count($parser->getAttachments()) > 0,
                    'date' => $parser->getHeader('date'),
                ];
            },
            $messages->toArray()
        );
    }

    #[LiveAction()]
    public function nextPage(): void
    {
        if ($this->getTotalMessages() > ($this->getEnd())) {
            $this->page++;
        }
    }

    #[LiveAction()]
    public function previousPage(): void
    {
        if ($this->page === 0) {
            return;
        }

        $this->page--;
    }

    public function getTotalMessages(): int
    {
        return $this->dm
            ->createQueryBuilder(Email::class)
            ->field('inbox.$id')
            ->equals(new ObjectId($this->inboxId))
            ->count()
            ->getQuery()
            ->execute();
    }

    public function getStart(): int
    {
        return ($this->page * self::DEFAULT_PER_PAGE) + 1;
    }

    public function getEnd(): int
    {
        return min($this->getStart() + (self::DEFAULT_PER_PAGE - 1), $this->getTotalMessages());
    }
}
