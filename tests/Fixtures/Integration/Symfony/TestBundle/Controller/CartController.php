<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Fixtures\Integration\Symfony\TestBundle\Controller;

use Elao\FormSimpleObjectMapper\Tests\Fixtures\Integration\Symfony\TestBundle\Form\Type\AddItemToCartType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;

class CartController extends Controller
{
    use VarDumperTestTrait;

    public function addItemAction(Request $request)
    {
        $form = $this->createForm(AddItemToCartType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = $form->getData();

            return Response::create($this->getDump($command));
        }

        return $this->render('TestBundle:cart:add_item.html.twig', [
            'form' => $form->createView(),
        ], Response::create(
            null,
            $form->isSubmitted() && !$form->isValid() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK
        ));
    }
}
