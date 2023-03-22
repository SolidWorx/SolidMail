<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'app_homepage')]
final class HomeController
{
    #[Template('homepage/index.html.twig')]
    public function __invoke(): array
    {
        return [];
    }
}
