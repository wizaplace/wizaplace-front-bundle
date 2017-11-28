# Wizaplace Front Bundle

A Symfony bundle giving you the tools to build your own Wizaplace front-office.

This repository contains low-level utilities. To build a Wizaplace front-office you may be interested instead in using our full Starter Kit. Please [contact us](https://www.wizaplace.com/).

## Installation

```
$ composer require wizaplace/front-bundle
```

You need to update your `app/AppKernel.php` file:

```php
<?php
// app/AppKernel.php

use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new WizaplaceFrontBundle\WizaplaceFrontBundle(),
        ];
    }
}
```

You also need to import routing:

```
# app/config/routing.yml

wizaplace_front_bundle:
    resource: '@WizaplaceFrontBundle/Resources/config/routing.yml'
```
