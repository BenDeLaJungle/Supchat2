<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 20; $i++) {
            $user = new Users();
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());

            $username = strtolower($user->getFirstName() . '.' . $user->getLastName());
            $user->setUserName($username);

            $user->setEmailAddress($username . '@example.com');

            // Générer un mot de passe hashé
            $plainPassword = 'Password123!';
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Rôle : 1 admin pour 4 users
            $user->setRole($i % 5 === 0 ? 'ROLE_ADMIN' : 'ROLE_USER');

            // Thème aléatoire (true ou false)
            $user->setTheme($faker->boolean());

            // Status fantaisie
            $user->setStatus($faker->randomElement(['Actif', 'Inactif', 'En attente']));

            // Simuler un utilisateur connecté via OAuth pour certains
            if ($faker->boolean(20)) { // 20% auront un provider OAuth
                $user->setOauthProvider($faker->randomElement(['google', 'facebook']));
                $user->setOauthID($faker->uuid());
            }

            $manager->persist($user);
        }

        $manager->flush();
    }
}
