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
$app->add(MethodOverrideMiddleware::class);

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
    $postData = $request->getParsedBodyParam('post');

    $validator = new Slim\Example\Validator();
    $errors = $validator->validate($postData);

    if (count($errors) === 0) {
        $repo->save($postData);
        $this->get('flash')->addMessage('success', 'Post has been created');
        $url = $router->urlFor('posts');
        return $response->withRedirect($url);
    }

    $params = [
        'postData' => $postData,
        'errors' => $errors
    ];

    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'posts/new.phtml', $params);
});

$app->get('/posts/{id}/edit', function ($request, $response, array $args) use ($repo) {
    $id = $args['id'];
    $post = $repo->find($id);
    $params = [
        'postData' => $post,
        'post' => $post
    ];
    return $this->get('renderer')->render($response, 'posts/edit.phtml', $params);
})->setName('editPost');

$app->patch('/posts/{id}', function ($request, $response, array $args) use ($repo, $router) {
    $id = $args['id'];
    $post = $repo->find($id);
    $data = $request->getParsedBodyParam('post');

    $validator = new Slim\Example\Validator();
    $errors = $validator->validate($data);

    if (count($errors) === 0) {
        $post['name'] = $data['name'];
        $post['body'] = $data['body'];

        $repo->save($post);
        $this->get('flash')->addMessage('success', 'Post has been updated');
        $url = $router->urlFor('posts');
        return $response->withRedirect($url);
    }

    $params = [
        'postData' => $data,
        'post' => $post,
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response->withStatus(422), 'posts/edit.phtml', $params);
});

$app->delete('/posts/{id}', function ($request, $response, array $args) use ($repo, $router) {
    $id = $args['id'];
    $repo->destroy($id);
    $this->get('flash')->addMessage('success', 'Post has been removed');

    return $response->withRedirect($router->urlFor('posts'));
});

$app->run();
