<?php

namespace App\Command;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:fixtures:nopurge', description: 'Charge les fixtures sans vider la base')]
class LoadFixturesNoPurgeCommand extends Command
{
    private EntityManagerInterface $em;
    private iterable $fixtures;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, iterable $fixtures, LoggerInterface $logger)
    {
        parent::__construct();
        $this->em = $em;
        $this->fixtures = $fixtures;
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $loader = new Loader();
        foreach ($this->fixtures as $fixture) {
            $loader->addFixture($fixture);
        }

        $executor = new ORMExecutor($this->em, new ORMPurger($this->em));
        $executor->setLogger($this->logger); 

        $executor->execute($loader->getFixtures(), true);

        $output->writeln('<fg=green>Fixtures chargées sans purge !</>');
        return Command::SUCCESS;
    }
}

