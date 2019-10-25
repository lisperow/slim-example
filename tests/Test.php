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
}
