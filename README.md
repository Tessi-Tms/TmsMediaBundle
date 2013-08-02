TmsMediaBundle
==============

Symfony2 media bundle


Installation
============

To install this bundle please follow the next steps:

First add the dependency in your `composer.json` file:

```json
"require": {
        ...,
        "tms/media-bundle": "dev-master"
    },
```

Then install the bundle with the command:

```sh
php composer update
```

Enable the bundle in your application kernel:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        //
        new Tms\Bundle\MediaBundle\TmsMediaBundle(),
    );
}
```
Now the Bundle is installed.

How to use it
=============

This bundle uses the storage service of [Gaufrette](https://github.com/KnpLabs/Gaufrette.git).

