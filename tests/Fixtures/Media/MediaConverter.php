<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Fixtures\Media;

use Elao\FormSimpleObjectMapper\DataMapper\FormDataToObjectConverterInterface;
use Elao\FormSimpleObjectMapper\DataMapper\ObjectToFormDataConverterInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class MediaConverter implements FormDataToObjectConverterInterface, ObjectToFormDataConverterInterface
{
    public function convertFormDataToObject(array $data, $originalData = null)
    {
        $author = $data['author'];

        switch ($data['mediaType']) {
            case 'movie':
                return new Movie($author);
            case 'book':
                return new Book($author);
            default:
                throw new TransformationFailedException();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param Media|null $object
     */
    public function convertObjectToFormData($object)
    {
        if (null === $object) {
            return [];
        }

        $mediaTypeByClass = [
            Movie::class => 'movie',
            Book::class => 'book',
        ];

        if (!isset($mediaTypeByClass[get_class($object)])) {
            throw new TransformationFailedException();
        }

        return [
            'mediaType' => $mediaTypeByClass[get_class($object)],
            'author' => $object->getAuthor(),
        ];
    }
}
