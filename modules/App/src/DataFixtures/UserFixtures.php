<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @see https://symfony.com/bundles/DoctrineFixturesBundle/current/index.html
 * @see https://www.doctrine-project.org/projects/doctrine-data-fixtures/en/1.7/how-to/fixture-ordering.html
 */
class UserFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        for($i = 0; $i < 10; $i++) {
            $user = new User(
                name: $faker->name(),
                email: $faker->email(),
            );
            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }
}
