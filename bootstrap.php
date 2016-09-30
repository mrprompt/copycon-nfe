<?php
use DerAlex\Silex\YamlConfigServiceProvider;
use MrPrompt\Silex\Router\Router as RouterServiceProvider;
use MrPrompt\Silex\NFe\Service as NFeServiceProvider;
use MrPrompt\Silex\Cors\Cors as CorsServiceProvider;
use Silex\Application;
use Silex\Provider\MonologServiceProvider;
use Symfony\Component\HttpFoundation\Response;

/**
 * @const string DS
 */
defined('DS') || define('DS', DIRECTORY_SEPARATOR);

/**
 * @const string APPLICATION_ENV
 */
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?: 'production'));

/**
 * Auto loader
 *
 * @var \Composer\Autoload\ClassLoader $loader
 */
$loader = require 'vendor' . DS . 'autoload.php';
$loader->register();

$routeFile = __DIR__ . DS . 'config' . DS . 'routes.yml';

/**
 * Silex Application
 *
 * @var Application $app
 */
$app            = new Application();
$app['exception_handler']->disable();

/* @var $configs array */
$configs = [
    __DIR__ . DS . 'config' . DS . 'nfe.yml',
    __DIR__ . DS . 'config' . DS . 'logger.yml',
    __DIR__ . DS . 'config' . DS . 'pagseguro.yml',
    __DIR__ . DS . 'config' . DS . 'eduzz.yml',
];

foreach ($configs as $config) {
    $app->register(new YamlConfigServiceProvider($config));
}

// Logger Service
$app->register(
    new MonologServiceProvider(),
    [
        'monolog.logfile'   => $app['config']['log']['logfile'],
        'monolog.permission'=> $app['config']['log']['permission'],
        'monolog.level'     => $app['config']['log']['level'],
        'monolog.name'      => $app['config']['log']['name'],
    ]
);

// Router Provider
$app->register(new RouterServiceProvider($routeFile));

// NFe.io Service
$app->register(new NFeServiceProvider($app['config']['nfe']['token'], $app['config']['nfe']['company']));

// CORS
$app->register(new CorsServiceProvider());

// Before event
$app->before(function ($request) {
    // Skipping OPTIONS requests
    if ($request->getMethod() === 'OPTIONS') {
        return;
    }

    // If body request is JSON, decode it!
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);

        $request->request->replace(is_array($data) ? $data : []);
    }
});

// Finish event
$app->finish(function() use ($app) {
    if (!$app->offsetExists('nfe.params') || !$app->offsetExists('response')) {
        return;
    }

    /* @var $fields array */
    $fields   = $app['nfe.params'];

    /* @var $response array */
    $response = $app['response'];

    try {
        /* @var $nfe \Nfe_ServiceInvoice */
        $nfe        = $app['nfe.create']($fields);
        $nfeResult  = $nfe->getAttributes();

        $response['nfe'] = print_r($nfeResult, true);
    } catch (\Exception $ex) {
        $app['logger']->addDebug($ex->getMessage());

        $response['nfe'] = false;
    }

    $app['logger']->addDebug(print_r($response, true));
});

// Error Handler
$app->error(
    function (\Exception $e, $code = Response::HTTP_INTERNAL_SERVER_ERROR) use ($app) {
        if ($e->getCode() !== 0) {
            $code = $e->getCode();
        }

        if ($code > 505 || $code < 100) {
            $code = 500;
        }

        return $app->json(["exception" => $e->getMessage(), "status" => "error"], $code);
    }
);

if (APPLICATION_ENV !== 'production') {
    $app['debug']   = true;
    $app['testing'] = true;
}


return $app;
