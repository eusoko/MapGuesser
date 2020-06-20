<?php namespace MapGuesser\Cli;

use MapGuesser\PersistentData\PersistentDataManager;
use MapGuesser\PersistentData\Model\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddUserCommand extends Command
{
    public function configure()
    {
        $this->setName('user:add')
            ->setDescription('Adding of user.')
            ->addArgument('email', InputArgument::REQUIRED, 'Email of user')
            ->addArgument('password', InputArgument::REQUIRED, 'Password of user')
            ->addArgument('type', InputArgument::OPTIONAL, 'Type of user');;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();
        $user->setEmail($input->getArgument('email'));
        $user->setPlainPassword($input->getArgument('password'));

        if ($input->hasArgument('type')) {
            $user->setType($input->getArgument('type'));
        }

        try {
            $pdm = new PersistentDataManager();
            $pdm->saveToDb($user);
        } catch (\Exception $e) {
            $output->writeln('<error>Adding user failed!</error>');
            $output->writeln('');

            $output->writeln((string) $e);
            $output->writeln('');

            return 1;
        }

        $output->writeln('<info>User was successfully added!</info>');

        return 0;
    }
}
