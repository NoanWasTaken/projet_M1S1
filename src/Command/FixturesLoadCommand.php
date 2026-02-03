<?php

namespace App\Command;

use App\Fixtures\GameTypesFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fixtures:load',
    description: 'Load all fixtures into the database',
)]
class FixturesLoadCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Loading GameTypes fixtures...');
        GameTypesFixtures::load($this->entityManager);
        $io->success('GameTypes fixtures loaded successfully!');

        return Command::SUCCESS;
    }
}
