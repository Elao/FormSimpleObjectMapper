<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) 2016 Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Fixtures\Integration\Symfony\TestBundle\Controller;

use Elao\FormSimpleObjectMapper\Tests\Fixtures\Media\Book;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\Media\Media;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\Media\MediaConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;

class MediaController extends Controller
{
    use VarDumperTestTrait;

    public function editAction(Request $request)
    {
        $media = new Book('John Doe');

        $builder = $this
            ->createFormBuilder($media, [
                'data_class' => Media::class,
                'simple_object_mapper' => new MediaConverter(),
            ])
            ->add('mediaType')
            ->add('author')
        ;

        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = $form->getData();

            return Response::create($this->getDump($command));
        }

        return $this->render('TestBundle:media:edit.html.twig', [
            'form' => $form->createView(),
        ], Response::create(
            null,
            $form->isSubmitted() && !$form->isValid() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK
        ));
    }
}
