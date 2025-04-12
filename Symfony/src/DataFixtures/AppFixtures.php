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

    //utilisateur admin
    $admin = new Users();
    $admin->setFirstName('Admin');
    $admin->setLastName('Test');
    $admin->setUserName('ADMIN');
    $admin->setEmailAddress('admin@example.com');

    $hashedPassword = $this->passwordHasher->hashPassword($admin, 'ADMIN');
    $admin->setPassword($hashedPassword);

    $admin->setRole('ROLE_ADMIN');
    $admin->setTheme(false);
    $admin->setStatus('Actif');

    $manager->persist($admin);

        for ($i = 1; $i <= 5; $i++) {
        $user = new Users();
        $user->setFirstName('User');
        $user->setLastName((string)$i);
        $user->setUserName("user$i");
        $user->setEmailAddress("user$i@example.com");
        $user->setPassword($this->passwordHasher->hashPassword($user, 'User123!'));
        $user->setRole('ROLE_USER');
        $user->setTheme(false);
        $user->setStatus('Actif');
        $manager->persist($user);
    }

    //utilisateur fixe
    for ($i = 1; $i <= 5; $i++) {
        $user = new Users();
        $user->setFirstName('User');
        $user->setLastName((string)$i);
        $user->setUserName("user$i");
        $user->setEmailAddress("user$i@example.com");
        $user->setPassword($this->passwordHasher->hashPassword($user, 'User123!'));
        $user->setRole('ROLE_USER');
        $user->setTheme(false);
        $user->setStatus('Actif');
        $manager->persist($user);
    }

    //utilisateurs aléatoires
    for ($i = 0; $i < 20; $i++) {
        $user = new Users();
        $user->setFirstName($faker->firstName());
        $user->setLastName($faker->lastName());

        $username = strtolower($user->getFirstName() . '.' . $user->getLastName());
        $user->setUserName($username);
        $user->setEmailAddress($username . '@example.com');

        $plainPassword = 'User123!';
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $user->setRole('ROLE_USER');
        $user->setTheme($faker->boolean());
        $user->setStatus($faker->randomElement(['Actif', 'Inactif', 'En attente']));

        if ($faker->boolean(20)) {
            $user->setOauthProvider($faker->randomElement(['google', 'facebook']));
            $user->setOauthID($faker->uuid());
        }

        $manager->persist($user);
    }

    $manager->flush();
    }
}