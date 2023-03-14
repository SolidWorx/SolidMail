<?php
declare(strict_types=1);

namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Types\Type;
use function bin2hex;

#[MongoDB\Document]
class Inbox implements \JsonSerializable, \Stringable
{
    #[MongoDB\Id]
    protected string $id;

    #[MongoDB\Field(type: Type::STRING)]
    protected string $name = '';

    #[MongoDB\Field(type: Type::STRING)]
    #[MongoDB\Index()]
    protected string $username;

    #[MongoDB\Field(type: Type::STRING)]
    #[MongoDB\Index()]
    protected string $password;

    #[MongoDB\ReferenceMany(
        targetDocument: Email::class,
    )]
    protected Collection $messages;

    public function __construct()
    {
        $this->username = bin2hex(random_bytes(10));
        $this->password = bin2hex(random_bytes(10));
        $this->messages = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function addMessage(Email $email): static
    {
        $this->messages->add($email);

        $email->setInbox($this);

        return $this;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
