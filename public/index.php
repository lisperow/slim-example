<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$repo = new Slim\Example\Repository();

$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'index.phtml');
});

$app->get('/posts', function ($request, $response) use ($repo) {
    $posts = $repo->all();
    $page = $request->getQueryParam('page', 1);
    $per = $request->getQueryParam('per', 5);
    $offset = ($page - 1) * $per;

    $sliceOfPosts = array_slice($posts, $offset, $per);
    $params = [
        'page' => $page
        'posts' => $sliceOfPosts
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
