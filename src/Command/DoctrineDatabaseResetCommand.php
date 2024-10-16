<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'doctrine:database:reset',
    description: 'Add a short description for your command',
)]
class DoctrineDatabaseResetCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }
    
    protected function configure()
    {
        $this
        ->setDescription('Reset the database by executing the reset_db.sh script.')
        ->setHelp('This command allows you to reset the database by executing the reset_db.sh shell script...')
        ->addOption('force', '-f', InputOption::VALUE_NONE, 'Force the reset without confirmation')
        ->addOption('datafixtures', '-d', InputOption::VALUE_NONE, "Include this option to load fixtures after resetting the database");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $scriptPath = __DIR__ . '/../../shell/resetdb.sh';

        if (!file_exists($scriptPath)) {
            $output->writeln('<error>The resetdb.sh script does not exist at the specified path.</error>');
            return Command::FAILURE;
        }

        if (!is_executable($scriptPath)) {
            $output->writeln('<error>The resetdb.sh script is not executable.</error>');
            return Command::FAILURE;
        }

        if (!$input->getOption('force')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<comment>Are you sure you want to reset the database? This action is irreversible. (yes/[no]) </comment>', false, '/^(y|yes)$/i');
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<comment>Database reset cancelled.</comment>');
                return Command::SUCCESS;
            }
        }

        $output->writeln('Resetting database...');
        
        $command = $scriptPath;
        if ($input->getOption('datafixtures')) {
            $command .= ' --datafixtures';
        }

        $process = Process::fromShellCommandline($command);
        $process->run();
    
        if (!$process->isSuccessful()) {
            $output->writeln('An error occurred during the reset.');
            $output->writeln($process->getErrorOutput());
            return Command::FAILURE;
        }
    
        $output->writeln('<info>Database reset successfully.</info>');
        return Command::SUCCESS;
    }


}
