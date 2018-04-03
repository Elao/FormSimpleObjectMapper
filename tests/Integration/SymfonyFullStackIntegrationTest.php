<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SymfonyFullStackIntegrationTest extends WebTestCase
{
    /** @var Client */
    private $client;

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    public function testAddItemToCartAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/cart/add-item');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertCount(1, $crawler->filter('form input[name="add_item_to_cart[quantity]"][type="number"]'));
        $this->assertCount(1, $crawler->filter('form input[name="add_item_to_cart[reference]"][type="hidden"]'));

        $form = $crawler->filter('form')->form();
        $form['add_item_to_cart[reference]'] = 'A000012';
        $form['add_item_to_cart[quantity]'] = 3;

        $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(<<<'DUMP'
Elao\FormSimpleObjectMapper\Tests\Fixtures\Integration\Symfony\Acme\Command\AddItemToCartCommand {
  -reference: "A000012"
  -quantity: 3
}
DUMP
            , $response->getContent());
    }

    public function testAddItemToCartActionFormError()
    {
        $crawler = $this->client->request(Request::METHOD_POST, '/cart/add-item', [
            'add_item_to_cart' => [
                'reference' => 'A000012',
                'quantity' => 'invalid_number',
            ],
        ]);
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertContains(
            'This value is not valid.',
            $crawler->filter('form input[name="add_item_to_cart[quantity]"][type="number"]')->parents()->html()
        );
    }

    public function testEditMediaAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/media/edit');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertCount(1, $crawler->filter('form input[name="form[author]"]'));
        $this->assertCount(1, $crawler->filter('form input[name="form[mediaType]"]'));

        $form = $crawler->filter('form')->form();
        $form['form[author]'] = 'Sarah Connor';
        $form['form[mediaType]'] = 'movie';

        $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(<<<'DUMP'
Elao\FormSimpleObjectMapper\Tests\Fixtures\Media\Movie {
  -author: "Sarah Connor"
}
DUMP
            , $response->getContent());
    }

    public function testEditMediaActionFormError()
    {
        $crawler = $this->client->request(Request::METHOD_POST, '/media/edit', [
            'form' => [
                'author' => 'Sarah Connor',
                'mediaType' => 'undefined',
            ],
        ]);
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertContains(
            'This value is not valid.',
            $crawler->filter('form > div > ul > li')->html()
        );
    }
}
