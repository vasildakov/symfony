<?php

namespace App\Controller;

use App\Event\CustomEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Home extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EventDispatcherInterface $dispatcher): Response
    {
        $dispatcher->dispatch(new CustomEvent('My custom event'));

        return $this->json(['username' => 'jane.doe']);
    }
}
