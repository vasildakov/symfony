<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/postgres/users', name: 'postgres.users')]
class Users
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        return new JsonResponse($users);
    }
}