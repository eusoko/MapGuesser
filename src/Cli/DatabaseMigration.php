<?php namespace MapGuesser\Cli;

use MapGuesser\Database\Query\Modify;
use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Database\IResultSet;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseMigration extends Command
{
    public function configure()
    {
        $this->setName('migrate')
            ->setDescription('Migration of database changes.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $db = \Container::$dbConnection;

        $db->startTransaction();

        $success = [];
        try {
            foreach ($this->readDir('structure') as $file) {
                $db->multiQuery(file_get_contents($file));

                $success[] = $this->saveToDB($file, 'structure');
            }

            foreach ($this->readDir('data') as $file) {
                require $file;

                $success[] = $this->saveToDB($file, 'data');
            }
        } catch (\Exception $e) {
            $db->rollback();

            $output->writeln('<error>Migration failed!</error>');
            $output->writeln('');

            $output->writeln((string) $e);
            $output->writeln('');

            return 1;
        }

        $db->commit();

        $output->writeln('<info>Migration was successful!</info>');
        $output->writeln('');

        if (count($success) > 0) {
            foreach ($success as $migration) {
                $output->writeln($migration);
            }

            $output->writeln('');
        }

        return 0;
    }

    private function readDir(string $type): array
    {
        $done = [];

        $migrationTableExists = \Container::$dbConnection->query('SELECT count(*)
            FROM information_schema.tables
            WHERE table_schema = \'' . $_ENV['DB_NAME'] . '\'
            AND table_name = \'migrations\';')
            ->fetch(IResultSet::FETCH_NUM)[0];

        if ($migrationTableExists != 0) {
            $select = new Select(\Container::$dbConnection, 'migrations');
            $select->columns(['migration']);
            $select->where('type', '=', $type);
            $select->orderBy('migration');

            $result = $select->execute();

            while ($migration = $result->fetch(IResultSet::FETCH_ASSOC)) {
                $done[] = $migration['migration'];
            }
        }

        $path = ROOT . '/database/migrations/' . $type;
        $dir = opendir($path);

        $files = [];
        while ($file = readdir($dir)) {
            $filePath = $path . '/' . $file;

            if (!is_file($filePath) || in_array(pathinfo($file, PATHINFO_FILENAME), $done)) {
                continue;
            }

            $files[] = $filePath;
        }

        natsort($files);

        return $files;
    }

    private function saveToDB(string $file, string $type): string
    {
        $baseName = pathinfo($file, PATHINFO_FILENAME);

        $modify = new Modify(\Container::$dbConnection, 'migrations');
        $modify->set('migration', $baseName);
        $modify->set('type', $type);
        $modify->save();

        return $baseName . ' (' . $type . ')';
    }
}
