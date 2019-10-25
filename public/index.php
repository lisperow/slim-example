<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$repo = new Slim\Example\Repository();
$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'index.phtml');
});

$app->get('/posts', function ($request, $response) use ($repo) {
    $flash = $this->get('flash')->getMessages();

    $params = [
        'flash' => $flash,
        'posts' => $repo->all()
    ];
    return $this->get('renderer')->render($response, 'posts/index.phtml', $params);
})->setName('posts');

$app->get('/posts/{id}', function ($request, $response, array $args) use ($repo) {
    $id = $args['id'];
    $post = $repo->find($id);
    if (!$post) {
        return $response->withStatusCode(404)->write('Page not found');
    }
    $params = [
        'post' => $post
    ];
    return $this->get('renderer')->render($response, 'posts/show.phtml', $params);
})->setName('post');

$app->get('/posts/new', function ($request, $response) {
    $params = [
        'postData' => [],
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'posts/new.phtml', $params);
})->setName('newPost');

$app->post('/posts', function ($request, $response) use ($router, $repo) {
    $post = $request->getParsedBodyParam('post');

    $validator = new App\Validator();
    $errors = $validator->validate($post);

    if (count($errors) === 0) {
        $repo->save($post);
        $this->get('flash')->addMessage('success', 'Post has been created');
        $url = $router->urlFor('posts');
        return $response->withRedirect($url);
    }

    $params = [
        'post' => $post,
        'errors' => $errors
    ];

    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'posts/new.phtml', $params);
});

$app->get('/users', function ($request, $response) use ($users) {
    $term = $request->getQueryParam('term');
    $result = collect($users)->filter(function ($user) use ($term) {
        return s($user['firstName'])->startsWith($term, false);
    });
    $params = [
        'term' => $term,
        'users' => $result
    ];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});

$app->run();
