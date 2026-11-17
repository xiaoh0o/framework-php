<?php
namespace App\Provider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Xiaoh0o\Database\Database;
use Xiaoh0o\Database\DatabaseInterface;
use Xiaoh0o\Database\MySQLiDatabase;
use Xiaoh0o\Utils\Configure;

class DatabaseProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        $array = [
            DatabaseInterface::class,
            MySQLiDatabase::class
        ];

        return in_array($id, $array, true);
    }

    public function register(): void
    {
        $config = Database::instance("default");
        $this->getContainer()->add(DatabaseInterface::class);
    }
}
