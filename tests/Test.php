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
        $response = $this->client->get('/posts');
        $body = $response->getBody()->getContents();

        $this->assertContains('Qui illo error nihil laborum vero', $body);
        $this->assertNotContains('Dicta voluptas fuga totam reiciendis qui', $body);

        $response2 = $this->client->get('/posts?page=2');
        $body2 = $response2->getBody()->getContents();
        $this->assertContains('?page=1', $body2);
        $this->assertContains('?page=3', $body2);

        $this->assertNotContains('Itaque quibusdam tempora velit porro ut velit soluta', $body2);
        $this->assertContains('Porro amet laborum iure molestiae', $body2);
    }

    public function testPost()
    {
        $response = $this->client->get("/posts/0b13e52d-b058-32fb-8507-10dec634a07c");
        $body = $response->getBody()->getContents();
        $this->assertContains('Quam ipsam voluptatem cupiditate sed natus debitis voluptas.', $body);
    }

    /**
     * @expectedException \GuzzleHttp\Exception\ClientException
     * @expectedExceptionMessage 404
     */
    public function testPostNotFound()
    {
        $this->client->get('/');
        $this->client->get('/post/undefined');
    }
}
