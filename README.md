TmsMediaBundle
==============

Symfony2 media bundle


Installation
============

To install this bundle please follow the next steps:

First add the dependency in your `composer.json` file:

```json
"repositories": [
    ...,
    {
        "type": "vcs",
        "url": "https://github.com/Tessi-Tms/TmsMediaBundle.git"
    }
],
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


API multipart request
=====================

The request to gather information from a media should look like the following : 

```http
http://your_domain/api/get logoPath@~/img.png
```

In this case we are using an example of multipart request using the [httpie](https://github.com/jkbr/httpie.git) syntax.

The structure of the storage
============================

You have to define the structure of your media directory in order to simplify the configuration of Gaufrette.

//TODO

Using gaufrette service
=======================

To handle the storage layer we are using Gaufrette, you can have a look on its documentation in order to configure your storage [Gaufrette bundle](https://github.com/KnpLabs/Gaufrette.git).


Using vichUploader service
==========================

To handle file upload, we use [Vich uploader bundle](https://github.com/dustin10/VichUploaderBundle.git) to make our entities uploadable.

