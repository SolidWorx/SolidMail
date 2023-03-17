<?php
declare(strict_types=1);

namespace App\Document;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Types\Type;

#[MongoDB\Document]
class Email
{
    #[MongoDB\Id]
    protected string $id;

    #[MongoDB\ReferenceOne(targetDocument: Inbox::class)]
    protected Inbox $inbox;

    #[MongoDB\Field(type: Type::STRING)]
    protected string $message;

    #[MongoDB\Field(type: Type::COLLECTION)]
    protected array $recipients;

    #[MongoDB\Field(type: Type::DATE_IMMUTABLE)]
    protected DateTimeInterface $dateReceived;

    public function __construct()
    {
        $this->dateReceived = new DateTimeImmutable();
    }

    public function getInbox(): Inbox
    {
        return $this->inbox;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setInbox(Inbox $inbox): static
    {
        $this->inbox = $inbox;

        return $this;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function setRecipients(array $recipients): void
    {
        $this->recipients = $recipients;
    }
}
