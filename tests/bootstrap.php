<?php

use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env.test')) {
    (new Dotenv())->load(dirname(__DIR__) . '/.env.test');
}

$_ENV['MESSENGER_TRANSPORT_DSN'] = $_ENV['MESSENGER_TRANSPORT_DSN'] ?? 'sync://';
$_SERVER['MESSENGER_TRANSPORT_DSN'] = $_SERVER['MESSENGER_TRANSPORT_DSN'] ?? 'sync://';

// Configuración del directorio de entidades y proxies
$paths = [dirname(__DIR__) . '/src/Entity'];
$isDevMode = true;

// Configuración de Doctrine ORM
$config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);

// Configuración de la conexión a la base de datos
$connectionParams = [
    'dbname' => $_ENV['DATABASE_NAME'] ?? 'formacion_test',
    'user' => $_ENV['DATABASE_USER'] ?? 'root',
    'password' => $_ENV['DATABASE_PASSWORD'] ?? 'bandit650n',
    'host' => $_ENV['DATABASE_HOST'] ?? '127.0.0.1',
    'driver' => 'pdo_mysql',
];

// Crear conexión
$connection = DriverManager::getConnection($connectionParams, $config);

// Crear EntityManager
return new EntityManager($connection, $config);
