# PHP Yandex Webmaster API (in progress)

[![Build Status](https://travis-ci.org/EvercodeLab/php-yandex-webmaster-api.png)](https://travis-ci.org/EvercodeLab/php-yandex-webmaster-api)

A PHP5 wrapper for [Yandex Webmaster API](http://api.yandex.ru/webmaster/).

## Installation

The easiest way to install `php-yandex-webmaster-api` is to use [Composer](http://getcomposer.org).

To install composer run:

```bash
$ curl -s http://getcomposer.org/installer | php
```

Add the following to your `composer.json`:

```yaml
{
    "require": {
        "evercodelab/yandex-webmaster-api": "dev-master"
    },
}
```

`php-yandex-webmaster-api` follows the PSR-0 convention names for its classes.

## ToDo

Wrapper is currently under development and by no means could not be claimed stable and reliable. Use at your own risk.

* Finish implementation of all API methods
* Add more tests
* Add documentation and usage examples
* Abstract HttpClient
* remove dependancy on `symfony/dom-crawler`?
* add caching?
* symfony bundle will follow

## License

`php-yandex-webmaster-api` is licensed under the MIT License - see the LICENSE file for details

## Credits

### Sponsored by

[![Evercode Lab](http://cl.ly/image/1Z3F1W0E0W0Z/content)](http://evercodelab.com)
