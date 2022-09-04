# [JAD](https://github.com/oligus/jad) (jsonapi.org) implementation in Slim 4 framework

Simplest way to have JSON:API endpoint in your Slim project

## Table of contents

- [Install](#install)
- [Usage](#usage)

## Install

Via Composer

``` bash
$ composer require benycode/slim-jad-middleware
```

Requires Slim 4.

## Usage

Use [DI](https://www.slimframework.com/docs/v4/concepts/di.html) to inject the library Middleware classes:

```php
use Psr\Container\ContainerInterface;
use Jad\Configure;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\DBAL\Configuration as DoctrineConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use BenyCode\Slim\JadMiddleware\JsonApiMiddleware;

return [
    ......
  JsonApiMiddleware::class => function (ContainerInterface $container): JadApiMiddleware {
		
    $config = Configure::getInstance();

      $config
        ->setConfig('strict', true)
        ;

      return new JsonApiMiddleware(
        $container->get(EntityManager::class), 
        $container->get(ResponseFactoryInterface::class),
        $config,
        '/api/json', // endpoint path, middleware will catch requests etc: /api/json/videos, /api/json/videos/1.....
      );
  },
  EntityManager::class => static function (ContainerInterface $container): EntityManager {
    /** @var array $settings */
    $settings = $container->get('settings');
    
    // Use the ArrayAdapter or the FilesystemAdapter depending on the value of the 'dev_mode' setting
    // You can substitute the FilesystemAdapter for any other cache you prefer from the symfony/cache library
    $cache = $settings['doctrine']['dev_mode'] ?
      DoctrineProvider::wrap(new ArrayAdapter()) :
      DoctrineProvider::wrap(new FilesystemAdapter(directory: $settings['doctrine']['cache_dir']));

      $config = Setup::createAttributeMetadataConfiguration(
        $settings['doctrine']['metadata_dirs'],
        $settings['doctrine']['dev_mode'],
        null,
        $cache
      );

      return EntityManager::create($settings['db'], $config);
  },
];
```

add a **Middlewares** to route globaly:

```php
use BenyCode\Slim\JadMiddleware\JsonApiMiddleware;

$app
  ...
  ->add(JsonApiMiddleware::class)
  ;
  ...
```

Doctrine config:

```php
$settings['db'] = [
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'dbname' => 'test',
    'user' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'driverOptions' => [
        // Turn off persistent connections
        PDO::ATTR_PERSISTENT => false,
        // Enable exceptions
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Emulate prepared statements
        PDO::ATTR_EMULATE_PREPARES => true,
        // Set default fetch mode to array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Set character set
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
    ],
];

$settings['doctrine'] = [
	// Enables or disables Doctrine metadata caching
	// for either performance or convenience during development.
	'dev_mode' => true,

	// Path where Doctrine will cache the processed metadata
	// when 'dev_mode' is false.
	'cache_dir' => APP_ROOT . '/../var/doctrine',

	// List of paths where Doctrine will search for metadata.
	// Metadata can be either YML/XML files or PHP classes annotated
	// with comments or PHP8 attributes.
	'metadata_dirs' => [APP_ROOT . '/../src/Domain'],
	
	'migrations' => [
		'migrations_paths' => [
			'App\\Doctrine\\Migration' => dirname(__DIR__) . '/resources/doctrine/migrations',
		],
		'custom_template' => dirname(__DIR__) . '/resources/doctrine/doctrine_migrations_class.php.tpl',
    ],
];

```
