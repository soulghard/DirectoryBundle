<?php

namespace CCETC\DirectoryBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppControllerTest extends WebTestCase
{
	public function testHomeLoads()
	{
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isSuccessful());
	}

    public function testAbout()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/about');

	    $client->getResponse()->isRedirect('http://ccetompkins.org/home/green-building/local-building-materials-initiative');
    }
}