<?php
declare(strict_types=1);

namespace App\Component;

use App\Document\Inbox;
use App\Document\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use function count;

#[AsLiveComponent('inbox_list')]
class InboxList
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?Inbox $selectedInbox = null;

    public function __construct(
        private readonly Security $security,
        private readonly DocumentManager $dm,
    ) {
    }

    public function getInboxes(): Collection
    {
        $user = $this->security->getUser();
        assert($user instanceof User);

        return $user->getInboxes();
    }

    #[LiveAction()]
    public function selectInbox(#[LiveArg] string $inbox): void
    {
        $this->selectedInbox = $this->dm->getRepository(Inbox::class)->find($inbox);
    }
}
