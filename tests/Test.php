<?php

namespace Slim\Example\Tests;

use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    private $client;

    public function setUp(): void
    {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'http://localhost:8080',
            'cookies' => true
        ]);
    }

    public function testPosts()
    {
        $this->client->get('/');
        $this->client->get('/posts');
        $response = $this->client->get('/posts/new');
        $body = $response->getBody()->getContents();
        $this->assertContains('post[name]', $body);
        $this->assertContains('post[body]', $body);

        $formParams = ['post' => ['name' => '', 'body' => '']];
        $response = $this->client->post('/posts', [
            /* 'debug' => true, */
            'form_params' => $formParams,
            'http_errors' => false
        ]);
        $this->assertEquals(422, $response->getStatusCode());
        $body = $response->getBody()->getContents();
        $this->assertContains("Can't be blank", $body);

        $formParams = ['post' => ['name' => 'second', 'body' => 'another']];
        $response = $this->client->post('/posts', [
            /* 'debug' => true, */
            'form_params' => $formParams
        ]);
        $body = $response->getBody()->getContents();
        $this->assertContains('Post has been created', $body);
        $this->assertContains('first', $body);
        $this->assertContains('second', $body);
    }

    public function testDeletePost()
    {
        $this->client->get('/');
        $this->client->get('/posts');
        $name = 'lalala';
        $formParams = ['post' => ['name' => $name, 'body' => 'last']];
        $response = $this->client->post('/posts', [
            /* 'debug' => true, */
            'form_params' => $formParams,
            'allow_redirects' => false
        ]);
        $id = $response->getHeaderLine('X-ID');
        $this->assertEquals(302, $response->getStatusCode());

        $response = $this->client->delete("/posts/{$id}", [
            /* 'debug' => true, */
            'allow_redirects' => false
        ]);
        $this->assertEquals(302, $response->getStatusCode());
        $response = $this->client->get('/posts');
        $body = $response->getBody()->getContents();
        $this->assertNotContains($name, $body);
    }
}
