<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/app/list/inboxes', name: 'app_list_inboxes')]
final class ListInboxesController
{
    #[Template('inbox/list.html.twig')]
    public function __invoke(): array
    {
        return [];
    }
}
