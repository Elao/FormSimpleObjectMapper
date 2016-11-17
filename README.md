Symfony Form Simple Object Mapper
=================================

[![Latest Stable Version](https://poser.pugx.org/elao/form-simple-object-mapper/v/stable?format=flat-square)](https://packagist.org/packages/elao/form-simple-object-mapper) 
[![Total Downloads](https://poser.pugx.org/elao/form-simple-object-mapper/downloads?format=flat-square)](https://packagist.org/packages/elao/form-simple-object-mapper) 
[![Monthly Downloads](https://poser.pugx.org/elao/form-simple-object-mapper/d/monthly?format=flat-square)](https://packagist.org/packages/elao/form-simple-object-mapper)
[![Latest Unstable Version](https://poser.pugx.org/elao/form-simple-object-mapper/v/unstable?format=flat-square)](https://packagist.org/packages/elao/form-simple-object-mapper)
[![License](https://poser.pugx.org/elao/form-simple-object-mapper/license?format=flat-square)](https://packagist.org/packages/elao/form-simple-object-mapper)
[![Build Status](https://img.shields.io/travis/Elao/FormSimpleObjectMapper/master.svg?style=flat-square)](https://travis-ci.org/Elao/FormSimpleObjectMapper)
[![Coveralls](https://img.shields.io/coveralls/Elao/FormSimpleObjectMapper.svg?style=flat-square)](https://coveralls.io/github/Elao/FormSimpleObjectMapper)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/Elao/FormSimpleObjectMapper.svg?style=flat-square)](https://scrutinizer-ci.com/g/Elao/FormSimpleObjectMapper/?branch=master)
[![Symfony](https://img.shields.io/badge/Symfony-%202.8%2F3.1%2B-green.svg?style=flat-square "Available for Symfony 2.8 and 3.1+")](https://symfony.com)
[![php](https://img.shields.io/badge/PHP-7-green.svg?style=flat-square "Available for PHP 7+")](http://php.net/)

This library aims to ease immutable or value objects mapping with the Symfony Form component, based on [Bernhard Schussek (Webmozart)](https://github.com/webmozart)'s blog post: ["Value Objects in Symfony Forms"](https://webmozart.io/blog/2015/09/09/value-objects-in-symfony-forms/), until a decision on https://github.com/symfony/symfony/pull/19367 is made.

Table of Contents
=================

  * [Installation](#installation)
    * [With Symfony Full Stack framework](#with-symfony-full-stack-framework)
    * [With Symfony Form component only](#with-symfony-form-component-only)
  * [Usage](#usage)
  * [Advanced usage](#advanced-usage)
    * [Using a callback](#using-a-callback)
    * [Handle conversion errors](#handle-conversion-errors)
    * [Convert form data to null](#convert-form-data-to-null)
    * [Map an object to the form](#map-an-object-to-the-form)

# Installation

```sh
$ composer require elao/form-simple-object-mapper
 ```

## With Symfony Full Stack framework

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Elao\FormSimpleObjectMapper\Bridge\Symfony\Bundle\ElaoFormSimpleObjectMapperBundle(),
        ];
    }

    // ...
}
```

## With Symfony Form component only

Register the type extension within the form factory, by using the `FormFactoryBuilder`:

```php
<?php

use Elao\FormSimpleObjectMapper\Type\Extension\SimpleObjectMapperTypeExtension;
use Symfony\Component\Form\Forms;
 
$builder = Forms::createFormFactoryBuilder();
$builder->addTypeExtension(new SimpleObjectMapperTypeExtension());
$factory = $builder->getFormFactory();
```

# Usage

The library aims to provide a solution to not modify your domain or application models only for satisfying the Symfony Form component requirements.  
**The way your classes are designed should not be denatured because of infrastructure constraints** (the libraries you're using in your project).

This is particularly true when using a command bus, such as [thephpleague/tactician](https://github.com/thephpleague/tactician).

Imagine a simple `AddItemToCartCommand` command:

```php
<?php

namespace Acme\Application\Cart\Command;

class AddItemToCartCommand
{
    /** @var string */
    private $reference;

    /** @var int */
    private $quantity;

    public function __construct($reference, $quantity)
    {
        $this->reference = $reference;
        $this->quantity = $quantity;
    }

    public function getReference()
    {
        return $this->reference;
    }
    
    public function getQuantity()
    {
        return $this->quantity;
    }
}
```

Your controller will look like:

```php
<?php

class CartController extends Controller
{
    //...

    public function addItemAction(Request $request)
    {
        $builder = $this
            ->createFormBuilder()
            ->add('reference', HiddenType::class)
            ->add('quantity', IntegerType::class)
        ;
        
        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $command = new AddItemToCartCommand($data['reference'], $data['quantity']);
            
            $this->getCommandBus()->handle($command);

            return $this->redirect(/*...*/);
        }

        return $this->render(':cart:add_item.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    //...
}
```

Although this works great, you're forced to create the `AddItemToCartCommand` object from form's data, and validation has been processed on raw form data instead of you're object. You're not manipulating objects inside form neither, which can be an issue when dealing with more complex forms and form events.

As the form is responsible to map the request to objects with a meaning in your app, it makes sense to delegate the creation of our command from the request to the Form component.  
Thus, you'll create a form type similar to the following one:

```php
<?php

class AddItemToCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference', HiddenType::class)
            ->add('quantity', IntegerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AddItemToCartCommand::class,
            'empty_data' => new AddItemToCart('', 1),
        ]);
    }
}
```

And your new controller:

```php
<?php

class CartController extends Controller
{
    //...

    public function addItemAction(Request $request)
    {
        $form = $this->createForm(AddItemToCartType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = $form->getData();
            
            $this->getCommandBus()->handle($command);

            return $this->redirect(/*...*/);
        }

        return $this->render(':cart:add_item.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    //...
}
```

Although it works perfectly for creating the form, this won't work natively when submitting it:

> Neither the property "reference" nor one of the methods "setReference()", "__set()" or "__call()" exist and have public access in class "AddItemToCartCommand".
 
This is explained by the fact the Form component uses by default the `PropertyPathMapper`, which tries to access and set object properties by using different means, as public getters/setters or public properties (It makes use of the [Symfony `PropertyAccess` component](http://symfony.com/doc/current/components/property_access.html) internally).

As most of our commands, `AddItemToCartCommand` is designed as an **immutable object**. It's meant to **preserve the command integrity** once created and validated, in order to safely process it inside our handlers. Hence, despite the fact the `PropertyPathMapper` is able to read properties through the provided getters, the command object does not have any setter. Thus, the `PropertyPathMapper` is unable to map submitted form datas to our object. We must tell the Form component how to proceed (see [Bernhard Schussek (Webmozart)'s blog post: "Value Objects in Symfony Forms"](https://webmozart.io/blog/2015/09/09/value-objects-in-symfony-forms/) for a complete exlanation and examples on how to achieve that with data mappers).

Of course you could add setters or make the command properties public to workaround this limitation, but as stated above:

> **Your classes should not be denatured because of infrastructure constraints.**

We've seen the `PropertyPathMapper` is perfectly able to read our object, and map its properties to the form. Hence come the new `SimpleObjectMapper` and `simple_object_mapper` option:

```php
<?php

class AddItemToCartType extends AbstractType implements FormDataToObjectConverterInterface // <-- Implement this interface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference', HiddenType::class)
            ->add('quantity', IntegerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AddItemToCartCommand::class,
            'simple_object_mapper' => $this, // <-- Set this option
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function convertFormDataToObject(array $data, $originalData)
    {
        // Tells the form how to build your object from its data:
        return new AddItemToCartCommand(
            $data['reference'],
            $data['quantity']
        );
    }
}
```

Your only job is to tell the form how to create an instance of your object according to submitted data, by implementing `FormDataToObjectConverterInterface::convertFormDataToObject(array $data, $originalData)`:

- The first `$data` argument of this method is an array of data submitted to the form.
- The second `$originalData` argument is the original data you gave to the form when creating it, which can be reused to recreate your object from data not present in the form itself.

This code is more or less the one we've written in the first controller version, but the logic is moved where it belongs: inside the form type.  
As a bonus, the object is properly validated by the Symfony Validator component.

# Advanced usage

## Using a callback

Instead of implementing the `FormDataToObjectConverterInterface`, you can simply pass a callable as the `simple_object_mapper` option value:

```php
<?php

class AddItemToCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference', HiddenType::class)
            ->add('quantity', IntegerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AddItemToCartCommand::class,
            'simple_object_mapper' => function (array $data, $originalData) {
                  // Tells the form how to build your object from its data:
                  return new AddItemToCartCommand(
                      $data['reference'],
                      $data['quantity']
                  );
            }
        ]);
    }
}
```

## Handle conversion errors

If you're unable to convert form's data to an object, due to unexpected or missing data, you should throw a [`TransformationFailedException`](http://api.symfony.com/master/Symfony/Component/Form/Exception/TransformationFailedException.html).  
This exception is gracefully handled by the form component by catching it and transforming it to a form error.
The error message displayed is the one set in the [`invalid_message`](http://symfony.com/doc/current/reference/forms/types/form.html#invalid-message) component.

Structural validation should be ensured by using proper form types (i.e: `IntegerType` for an integer field) and domain validation by validation rules using teh Symfony Validator component.

## Convert form data to null

When it makes sense, it's up to you to add your own logic inside `FormDataToObjectConverterInterface::convertFormDataToObject()` in order to return `null` instead of an instance of your object according to submitted data:

```php
<?php

class MoneyType extends AbstractType implements FormDataToObjectConverterInterface 
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', NumberType::class)
            ->add('currency')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Money::class,
            'simple_object_mapper' => $this,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @param Money|null $originalData
     */
    public function convertFormDataToObject(array $data, $originalData)
    {
        // Logic to determine if the result should be considered null according to form fields data.
        if (null === $data['amount'] && null === $data['currency']) {
            return null;
        }

        return new Money($data['amount'], $data['currency']);
    }
}

# Money.php
class Money
{
    private $amount;
    private $currency;

    public function __construct($amount, $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount() // ...
    public function getCurrency() // ...
}
```

## Map an object to the form

Mapping the object to the form is usually not something you should care if your immutable object has proper getters. The default `PropertyPathhMapper` implementation will do the job perfectly.

However, for most advanced usages, an `ObjectToFormDataConverterInterface` interface can also be implemented, allowing to skip the original mapper (in most cases the `PropertyPathMapper`) implementation, allowing to map the data to the form yourself by converting to value object to an array of form data indexed by field name:

```php
<?php

class MediaConverter implements FormDataToObjectConverterInterface, ObjectToFormDataConverterInterface 
{
    // ...

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
            throw new TransformationFailedException('Unexpected object class');
        }

        // The returned array will be used to set data in each form fields identified by keys.
        return [
            'mediaType' => $mediaTypeByClass[get_class($object)],
            'author' => $object->getAuthor(),
        ];
    }
}
```

> :memo: Remember, the `TransformationFailedException` message is not used to render the form error. It'll use the `invalid_message` option value instead. However, it's useful to set it for debugging purpose.

> :v: By using a proper `ChoiceType` field, this exception should never occur and a proper message will be shown about the unexpected field value.
