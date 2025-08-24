<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Slim\Flash\Messages;
use Slim\Middleware\Session;

require __DIR__ . '/../vendor/autoload.php';

$container = new DI\Container();

$container->set(\PDO::class, function () {
    $conn = new \PDO('sqlite:' . __DIR__ . '/../db/database.sqlite');
    $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

    $initSql = file_get_contents(__DIR__ . '/../init.sql');
    $conn->exec($initSql);

    return $conn;
});

$container->set('renderer', function () {
    return new PhpRenderer(__DIR__ . '/../templates');
});

$container->set('session', function () {
    return new \Slim\Middleware\Session([
        'name' => 'slim_session',
        'autorefresh' => true,
        'lifetime' => '1 hour'
    ]);
});

$container->set('flash', function () {
    return new Messages();
});

// ПРАВИЛЬНАЯ регистрация CarRepository
$container->set(\App\CarRepository::class, function ($container) {
    return new \App\CarRepository($container->get(\PDO::class));
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

// Добавляем middleware для сессий
$app->add(new Session([
    'name' => 'slim_session',
    'autorefresh' => true,
    'lifetime' => '1 hour'
]));

// Маршруты
$app->get('/cars', function (Request $request, Response $response) {
    $carRepository = $this->get(\App\CarRepository::class);
    $cars = $carRepository->getEntities();

    $messages = $this->get('flash')->getMessages();

    $params = [
        'cars' => $cars,
        'flash' => $messages
    ];

    return $this->get('renderer')->render($response, 'cars/index.phtml', $params);
})->setName('cars.index');

$app->get('/cars/new', function (Request $request, Response $response) {
    $params = [
        'car' => [],
        'errors' => []
    ];

    return $this->get('renderer')->render($response, 'cars/new.phtml', $params);
})->setName('cars.create');

$app->post('/cars', function (Request $request, Response $response) {
    $carRepository = $this->get(\App\CarRepository::class);
    $carData = $request->getParsedBody()['car'] ?? [];

    $validator = new \App\CarValidator();
    $errors = $validator->validate($carData);

    if (count($errors) === 0) {
        $car = new \App\Car($carData['make'], $carData['model']);
        $carRepository->save($car);
        $this->get('flash')->addMessage('success', 'Car was added successfully');
        return $response->withHeader('Location', '/cars')->withStatus(302);
    }

    $params = [
        'car' => $carData,
        'errors' => $errors
    ];

    return $this->get('renderer')->render($response->withStatus(422), 'cars/new.phtml', $params);
})->setName('cars.store');

$app->post('/cars/{id}/delete', function (Request $request, Response $response, array $args) {
    $carRepository = $this->get(\App\CarRepository::class);
    $id = (int)$args['id'];

    if ($carRepository->delete($id)) {
        $this->get('flash')->addMessage('success', 'Car was deleted successfully');
    } else {
        $this->get('flash')->addMessage('error', 'Car not found');
    }

    return $response->withHeader('Location', '/cars')->withStatus(302);
})->setName('cars.delete');

$app->run();