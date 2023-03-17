<?php
declare(strict_types=1);

namespace App\Document;

use App\Validator\UniqueUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Types\Type;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[MongoDB\Id]
    protected string $id;

    #[MongoDB\Field(type: Type::STRING)]
    #[MongoDB\UniqueIndex()]
    #[Assert\NotBlank()]
    #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_STRICT)]
    #[UniqueUser()]
    protected string $email;

    #[MongoDB\Field(type: Type::STRING)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 8)]
    protected string $password;

    #[MongoDB\Field(type: Type::HASH)]
    protected array $roles = [];

    #[MongoDB\ReferenceMany(
        targetDocument: Inbox::class,
    )]
    protected Collection $inboxes;

    public function __construct()
    {
        $this->inboxes = new ArrayCollection();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getInboxes(): Collection
    {
        return $this->inboxes;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRoles(): array
    {
        return array_merge(['ROLE_USER'], $this->roles);
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function addInbox(Inbox $inbox): static
    {
        $this->inboxes->add($inbox);

        return $this;
    }
}
