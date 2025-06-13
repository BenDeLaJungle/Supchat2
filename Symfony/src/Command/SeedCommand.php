<?php

namespace App\Command;

use App\Entity\Users;
use App\Entity\Workspaces;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed',
    description: 'Crée les données initiales nécessaires pour le bon fonctionnement du projet.',
)]
class SeedCommand extends Command
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $hasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $hasher)
    {
        parent::__construct();
        $this->em = $em;
        $this->hasher = $hasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //Création de l admin (ID 1)
        $admin = $this->em->getRepository(Users::class)->find(1);

        if (!$admin) {
            $admin = new Users();

            $admin->setFirstName('Admin');
            $admin->setLastName('Principal');
            $admin->setUserName('admin');
            $admin->setEmailAddress('admin@example.com');
            $admin->setRole('ROLE_ADMIN');
            $admin->setTheme(true);
            $admin->setStatus('active');

            $hashedPassword = $this->hasher->hashPassword($admin, 'ADMIN');
            $admin->setPassword($hashedPassword);

            $this->em->persist($admin);
            $output->writeln('<info>✔ Utilisateur admin créé.</info>');
        } else {
            $output->writeln('<comment>Utilisateur admin déjà présent (ID 1).</comment>');
        }

        //Création du workspace privé (ID 1)
        $workspace = $this->em->getRepository(Workspaces::class)->find(1);

        if (!$workspace) {
            $workspace = new Workspaces();

            $workspace->setName('Messages Privés');
            $workspace->setStatus(false);
            $workspace->setCreator($admin);

            $this->em->persist($workspace);
            $output->writeln('<info>Workspace "Messages Privés" créé.</info>');
        } else {
            $output->writeln('<comment>Workspace déjà présent (ID 1).</comment>');
        }

        $this->em->flush();

        $output->writeln('<fg=green>Seed terminé avec succès !</>');

        return Command::SUCCESS;
    }
}
