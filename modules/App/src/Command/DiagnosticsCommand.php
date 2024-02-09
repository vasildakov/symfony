<?php

namespace App\Command;

use App\Diagnostics\Reporter\SymfonyConsole;
use Laminas\Diagnostics\Check;
use Laminas\Diagnostics\Runner\Reporter\BasicConsole;
use Laminas\Diagnostics\Runner\Runner;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(name: 'app:diagnostics', description: 'Running diagnostics tests')]
class DiagnosticsCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runner = new Runner();

        // PHP
        $runner->addCheck(new Check\PhpVersion('8.1', '>'));

        // PHP Extensions
        $runner->addCheck(new Check\ExtensionLoaded(['intl', 'pdo', 'pdo_pgsql', 'zip', 'mongodb']));

        // Nginx
        $nginx = new Check\HttpService('symfony-web-1', 80);
        $nginx->setLabel('Nginx');
        $runner->addCheck($nginx);

        // Directories
        $runner->addCheck(new Check\DirReadable('/code/public'));
        $runner->addCheck(new Check\DirWritable('/code/var'));
        $runner->addCheck(new Check\DirWritable('/tmp'));
        $runner->addCheck(new Check\DiskFree(100000000, '/tmp'));

        // Mongo
        $mongo = new Check\Mongo('mongodb://user:pass@mongodb:27017');
        $mongo->setLabel('MongoDB is working.');
        $runner->addCheck($mongo);

        // PostgreSQL
        $postgres = new Check\Callback(function () {
            $connection_string = sprintf(
                "host=symfony-database-1 port=5432 user=%s password=%s",
                $_ENV['POSTGRES_USER'],
                $_ENV['POSTGRES_PASSWORD']
            );

            $pgc = \pg_connect($connection_string);
            if ($pgc instanceof \PgSql\Connection) {
                return new Success('Server is working.');
            }
            return new Failure('Can not connect to PostgreSQL');
        });
        $postgres->setLabel('PostgreSQL');
        $runner->addCheck($postgres);


        // Add console reporter
        $runner->addReporter(new SymfonyConsole($input, $output));

        // Run all checks
        $results = $runner->run();

        return ($results->getFailureCount() + $results->getWarningCount()) > 0
            ? Command::FAILURE
            : Command::SUCCESS;
    }
}
