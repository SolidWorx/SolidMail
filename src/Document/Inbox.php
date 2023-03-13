<?php
declare(strict_types=1);

namespace App\Document;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Types\Type;

#[MongoDB\Document]
class Inbox implements \JsonSerializable
{
    #[MongoDB\Id]
    protected string $id;

    #[MongoDB\Field(type: Type::STRING)]
    protected string $name;

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
}
