<?php

namespace App\DataFixtures;

use App\Entity\Delivery;
use App\Entity\DeliveryProduct;
use App\Entity\Product;
use App\Service\PasswordHashService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class DeliveryProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        for ($i = 0; $i < 90; $i++) {
            $delivery = $this->getReference('delivery-' . $i, Delivery::class);
            $deliveryCompany = $delivery->getCompany();
            $productRepository = $manager->getRepository(Product::class);
            $products = $productRepository->findBy(['company' => $deliveryCompany]);
            $product = $products[array_rand($products)];
            $deliveryProduct = new DeliveryProduct();
            $deliveryProduct->setDelivery($delivery);
            $deliveryProduct->setProduct($product);
            $deliveryProduct->setQuantity($faker->numberBetween(1, 1000));
            $manager->persist($deliveryProduct);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            DeliveryFixtures::class,
            ProductFixtures::class
        ];
    }
}
