<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hello/{name}', name: 'hello')]
class Hello
{
    public function __invoke(string $name = 'World'): Response
    {
        return new Response(sprintf('Hello %s!', $name));
    }
}
