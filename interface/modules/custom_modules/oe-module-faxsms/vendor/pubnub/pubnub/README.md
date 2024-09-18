# PubNub PHP SDK (V4)

[![Build Status](https://travis-ci.com/pubnub/php.svg?branch=master)](https://travis-ci.com/pubnub/php)
[![codecov](https://codecov.io/gh/pubnub/php/branch/master/graph/badge.svg)](https://codecov.io/gh/pubnub/php)
[![Docs](https://img.shields.io/badge/docs-online-blue.svg)](https://www.pubnub.com/docs/php/pubnub-php-sdk)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/pubnub/php)

This is the official PubNub PHP SDK repository.

PubNub takes care of the infrastructure and APIs needed for the realtime communication layer of your application. Work on your app's logic and let PubNub handle sending and receiving data across the world in less than 100ms.

The SDK supports PHP 7.4 and 8.x.

## Get keys

You will need the publish and subscribe keys to authenticate your app. Get your keys from the [Admin Portal](https://dashboard.pubnub.com/login).

## Configure PubNub

1. Integrate the PHP SDK into your project:

   * Without composer

     1. Clone the following repository: `git clone https://github.com/pubnub/php.git ./pubnub-php`
     2. Copy the `src` folder to your project.
     3. Include `autoloader.php` file in your project:

         ```php
         require_once('src/autoloader.php');
         ```

     4. Download dependency `monolog` from [https://github.com/Seldaek/monolog](https://github.com/Seldaek/monolog) and copy the `monolog` folder from the `src` folder to the `src` folder of your project.
     5. Download dependency `psr/Log` from [https://github.com/php-fig/log/tree/master](https://github.com/php-fig/log/tree/master) and copy the `psr` folder to the `src` folder of your project.
     6. Download dependency `rmccue` from [https://github.com/WordPress/Requests](https://github.com/WordPress/Requests) and copy the `Requests` folder and the file `Requests.php` from the `library` folder to the `src` folder of your project.

   * With composer

     1. Add the PubNub package to your `composer.json` file:

         ```json
         {
             "require": {
                 <!-- include the latest version from the badge at the top -->
                 "pubnub/pubnub": "6.3.0"
             }
         }
         ```

     2. Run `composer install --no-dev‌` from the command line. This installs the PubNub PHP SDK and all its dependencies in the `vendor` folder of the project.

     3. Include `autoload.php` file in your project:

         ```php
         require_once('vendor/autoload.php');‌
         ```


2. Configure your keys:

    ```php
    $pnconf = new PNConfiguration();
    $pubnub = new PubNub($pnconf);

    $pnconf->setSubscribeKey("mySubscribeKey");
    $pnconf->setPublishKey("myPublishKey");
    $pnconf->setUserId("ReplaceWithYourClientIdentifier");
    ```

## Add event listeners

```php
class MySubscribeCallback extends SubscribeCallback {
    function status($pubnub, $status) {
        if ($status->getCategory() === PNStatusCategory::PNUnexpectedDisconnectCategory) {
        // This event happens when radio / connectivity is lost
        } else if ($status->getCategory() === PNStatusCategory::PNConnectedCategory){
        // Connect event. You can do stuff like publish, and know you'll get it // Or just use the connected event to confirm you are subscribed for // UI / internal notifications, etc
        } else if ($status->getCategory() === PNStatusCategory::PNDecryptionErrorCategory){
        // Handle message decryption error. Probably client configured to // encrypt messages and on live data feed it received plain text.
        }
    }
    function message($pubnub, $message){
    // Handle new message stored in message.message
    }
    function presence($pubnub, $presence){
    // handle incoming presence data
    }
}

$subscribeCallback = new MySubscribeCallback();
$pubnub->addListener($subscribeCallback);
```

## Publish/subscribe

```php
$pubnub->subscribe()
    ->channels("hello_world")
    ->execute();

$pubnub->publish()
    ->channel("hello_world")
    ->message("Hello PubNub!")
    ->sync();
```

## Documentation

* [API reference for PHP ](https://www.pubnub.com/docs/sdks/php)


## Support

If you **need help** or have a **general question**, contact support@pubnub.com.
