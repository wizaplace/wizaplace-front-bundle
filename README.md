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

## Using SSO API guard authentication

If you have an API KEY and its userId, you can login that user (available for customers only) in your front using a token in a request header.

1. Add the _TokenGuardAuthenticator_ on your _security.yml_ as explained in [the symfony doc](https://symfony.com/doc/current/security/guard_authentication.html#step-3-configure-the-authenticator) :

```
guard:
    authenticators:
        - WizaplaceFrontBundle\Security\FormGuardAuthenticator 
```

2. Declare an environment variable `CROSS_AUTHENTICATION_KEY` with a private key of your choice, which will be used later to encrypt and decrypt the user's token.
You must give that key to the third-party application who wants to send request in your front. It is a private key, keep it server-side !


3. Tell the third-party application how to generate a token: they must use blowfish (They can use [phpseclib](https://github.com/phpseclib/phpseclib) to do that) 
and they must encrypt a string built with the userId and the user apiKey in this format `userId:apiKey`, for example : 

```php
<?php 

use phpseclib\Crypt\Blowfish;

$cipher = new Blowfish();
$cipher->setKey('YOUR PRIVATE KEY'); // the CROSS_AUTHENTICATION_KEY

// They want to log that user
$userId = 1;
$apiKey = 'userApiKey';

// generate the string to crypt : 
$token = $userId.':'.$apiKey;

$cryptedToken = $cipher->encrypt($token);

```

4. They must use that token in a header called `x-cross-authentication` to send requests to your front with the http client of their choice. Note: header names are case-insensitive, so they can name the header X-Cross-Authentication for example.

