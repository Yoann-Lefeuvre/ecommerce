<?php

namespace App\DataFixtures;

use App\Entity\Images;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ImagesFixtures extends Fixture implements DependentFixtureInterface  // implementation d'une interface pour l'ordre d'execution des fixtures lors du load
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        for($img =  1; $img <= 100; $img++)
        {
            $image = new Images();        
            $image->setName($faker->image(null, 640, 480));
            
            $product = $this->getReference('prod-'.rand(1, 10));
            $image->setProducts($product);
            $manager->persist($image);

        }
        $manager->flush();
    }

    public function getDependencies(): array  // Méthode réclamée par l'interface DependentFixtureIntyerface
    {
        return [
            ProductsFixtures::class   //tableaux des fixtures chargées avant ImageFixtures
        ];
    }
}
