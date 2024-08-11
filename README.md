# Laravel SAML

[![License](https://img.shields.io/packagist/l/symfony/symfony)](https://packagist.org/packages/symfony/symfony)
[![PHP Version Require](https://img.shields.io/packagist/php-v/symfony/symfony)](https://packagist.org/packages/symfony/symfony)

## Installation

Install the package by the following command, (try with `--dev` if you want to install it on dev environment)

    composer require oktalogin/saml-okta-login

## Publish the Config

Run the following command to publish config file,

    php artisan vendor:publish --provider="Oktalogin\SamlOktaLogin\OktaLoginServiceProvider" --tag=config

## Add Provider

Add the provider to your `config/app.php` into `provider` section if using lower version of laravel,

    Oktalogin\SamlOktaLogin\OktaLoginServiceProvider::class,

After publishing, users can customize the config/saml.php file as needed.

## Run Composer Dump-Autoload:

    composer dump-autoload

### License

The MIT License (MIT). Please see [License](LICENSE.md) File for more information