<?php

namespace App\DataFixtures;

use App\Document\Product;
use Doctrine\Bundle\MongoDBBundle\Fixture\FixtureGroupInterface;
use Doctrine\Bundle\MongoDBBundle\Fixture\ODMFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures implements ODMFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $product = new Product('iPhone 15', 1500.00);
        $manager->persist($product);
        $manager->flush();

    }
}