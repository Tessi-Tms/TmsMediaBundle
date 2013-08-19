TmsMediaBundle
==============

The MediaBundle for Symfony2 provides an API to upload and retrieve media.


Installation
------------

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
-------------

### Definition of the webservices

#### Create a media

| Route           | Method | Parameters             | Header
|-----------------|--------|------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /media          | POST   | file={fileContent}    | Content-type=multipart/form-data

**Parameters description:**

- *file* : Contains the file content.

#### Delete a media

| Route                 | Method | Parameters         | Header
|-----------------------|--------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /media/{mediaId}      | DELETE |                    |

**Parameters description:**

- *mediaId*: The id of the media.

#### Get a media

| Route                 | Method | Parameters         | Header
|-----------------------|--------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /media/{mediaId}      | GET    |                    |

**Parameters description:**

- *mediaId*: The id of the media.

### Configure your filesystems

The following is a local sample configuration for the KnpGaufretteBundle. It will create a filesystem service called **gaufrette_gallery_filesyytem** which can be used in the MediaBundle.


```php

# app/config/config.yml
knp_gaufrette:
    adapters:
        gallery:
            local:
                directory: %kernel.root_dir%/../web/uploads
                create: true

    filesystems:
        gallery:
            adapter: gallery
```

For a complete list of features refer to the [officiel documentation](https://github.com/KnpLabs/Gaufrette.git).

//Work in progress
