<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class IndexControllerTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testIndex()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#app')->count());
    }
}
