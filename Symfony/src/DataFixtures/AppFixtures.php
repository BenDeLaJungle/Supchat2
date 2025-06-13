<?php

namespace App\DataFixtures;

use App\Entity\Users;
use App\Entity\Channels;
use App\Entity\Messages;
use App\Entity\Workspaces;
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

        // 1. Utilisateurs
        $users = [];

        // Admin
        $admin = $manager->getRepository(Users::class)->findOneBy(['emailAddress' => 'admin@example.com']);

        if (!$admin) {
            throw new \LogicException('L\'utilisateur admin (admin@example.com) doit être seedé avant de lancer les fixtures.');
        }

        // Utilisateurs fixes
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
            $users[] = $user;
        }

        // Utilisateurs aléatoires
        for ($i = 0; $i < 20; $i++) {
            $user = new Users();
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $username = strtolower($user->getFirstName() . '.' . $user->getLastName());
            $user->setUserName($username);
            $user->setEmailAddress($username . '@example.com');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'User123!'));
            $user->setRole('ROLE_USER');
            $user->setTheme($faker->boolean());
            $user->setStatus($faker->randomElement(['Actif', 'Inactif', 'En attente']));
            if ($faker->boolean(20)) {
                $user->setOauthProvider($faker->randomElement(['google', 'facebook']));
                $user->setOauthID($faker->uuid());
            }
            $manager->persist($user);
            $users[] = $user;
        }

        $manager->flush();

        // 2. Workspace
        $workspace = new Workspaces();
        $workspace->setName('Stratégie de communication');
        $workspace->setStatus(true);
        $workspace->setCreator($admin);
        $manager->persist($workspace);

        // 3. Channels
        $channels = [];
        for ($i = 1; $i <= 3; $i++) {
            $channel = new Channels();
            $channel->setName("Canal $i");
            $channel->setStatus(true);
            $channel->setWorkspace($workspace);
            $manager->persist($channel);
            $channels[] = $channel;
        }

        // 4. Messages
        foreach ($channels as $channel) {
            for ($i = 0; $i < 10; $i++) {
                $message = new Messages();
                $message->setChannel($channel);
                $message->setUser($faker->randomElement($users));
                $message->setContent($faker->sentence());
                $message->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($message);
            }
        }

        $manager->flush();
    }
}
