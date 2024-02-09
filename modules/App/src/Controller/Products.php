<?php

namespace App\Controller;

use App\Document\Product;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mongo/products', name: 'mongo.products')]
class Products
{
    public function __construct(
        private readonly DocumentManager $dm
    ) {
    }

    public function __invoke(): Response
    {
        $products = $this->dm
            ->getRepository(Product::class)
            ->findAll()
        ;

        return new JsonResponse($products);
    }
}
