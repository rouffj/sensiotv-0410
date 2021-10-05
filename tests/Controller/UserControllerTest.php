<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'SensioTV');
    }

    public function testRegisterForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $client->clickLink('Register');
        $this->assertSelectorTextContains('h1', 'Create your account');
        $registerForm = $client->getCrawler()->selectButton('Create your SensioTV account')->form();

        $client->submit($registerForm, [
            'user[firstName]' => 'Joseph',
            'user[email]' => 'joseph1@joseph.io'
        ]);

        //print_r($client->getResponse()->getContent());die;

        $this->assertCount(3, $client->getCrawler()->filter('.form-error-icon'));
    }
}
