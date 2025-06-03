<?php

namespace App\Command;

use App\Entity\Roles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed:roles',
    description: 'Crée les rôles de base pour le projet.',
)]
class SeedRolesCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rolesData = [
            ['name' => 'Member', 'publish' => 1, 'moderate' => 0, 'manage' => 0],
            ['name' => 'Moderator', 'publish' => 1, 'moderate' => 1, 'manage' => 0],
            ['name' => 'Admin', 'publish' => 1, 'moderate' => 1, 'manage' => 1],
        ];

        foreach ($rolesData as $roleData) {
            $existingRole = $this->em->getRepository(Roles::class)
                ->findOneBy(['name' => $roleData['name']]);

            if (!$existingRole) {
                $role = new Roles();
                $role->setName($roleData['name']);
                $role->setPublish($roleData['publish']);
                $role->setModerate($roleData['moderate']);
                $role->setManage($roleData['manage']);

                $this->em->persist($role);
                $output->writeln("<info>Rôle {$roleData['name']} créé.</info>");
            } else {
                $output->writeln("<comment>Rôle {$roleData['name']} déjà présent.</comment>");
            }
        }

        $this->em->flush();

        $output->writeln('<fg=green>Seed des rôles terminé avec succès !</>');

        return Command::SUCCESS;
    }
}