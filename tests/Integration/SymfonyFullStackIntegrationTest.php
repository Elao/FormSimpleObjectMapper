<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) 2016 Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SymfonyFullStackIntegrationTest extends WebTestCase
{
    public function testAddItemAction()
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/cart/add-item');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertCount(1, $crawler->filter('form input[name="add_item_to_cart[quantity]"][type="number"]'));
        $this->assertCount(1, $crawler->filter('form input[name="add_item_to_cart[reference]"][type="hidden"]'));

        $form = $crawler->filter('form')->form();
        $form['add_item_to_cart[reference]'] = 'A000012';
        $form['add_item_to_cart[quantity]'] = 3;

        $client->submit($form);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(<<<'DUMP'
Elao\FormSimpleObjectMapper\Tests\Fixtures\Integration\Symfony\Acme\Command\AddItemToCartCommand {
  -reference: "A000012"
  -quantity: 3
}
DUMP
            , $response->getContent());
    }

    public function testAddItemActionFormError()
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/cart/add-item');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertCount(1, $crawler->filter('form input[name="add_item_to_cart[quantity]"][type="number"]'));
        $this->assertCount(1, $crawler->filter('form input[name="add_item_to_cart[reference]"][type="hidden"]'));

        $form = $crawler->filter('form')->form();
        $form['add_item_to_cart[reference]'] = 'A000012';
        $form['add_item_to_cart[quantity]'] = 'invalid_number';

        $crawler = $client->submit($form);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertContains(
            'This value is not valid.',
            $crawler->filter('form input[name="add_item_to_cart[quantity]"][type="number"]')->parents()->html()
        );
    }
}
