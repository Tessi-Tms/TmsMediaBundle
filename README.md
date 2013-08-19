TmsMediaBundle
==============

Media bundle provides an API for media


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

| Route           | Method | Parameters         | Header
|-----------------|--------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /medias         | POST   | field@/dir/file    | multipart/form-data

**Parameters description:**

- *field* : File field, it presence results in a multipart/form-data request.
- *@* : File separator.
- *dir* : The directory where the file will be saved.
- *file* : The file to save.

**Parameters examples:**

``` html
    logoPath@~/Documents/cv.pdf
    screenshot@~/Pictures/img.png
    ...
```

#### Delete a media

| Route                 | Method | Parameters         | Header
|-----------------------|--------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /medias/{mediaId}     | DELETE |                    |

**Parameters description:**

- *mediaId*: The id of the media.

#### Get a media

| Route                 | Method | Parameters         | Header
|-----------------------|--------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /medias/{mediaId}     | GET    |                    |

**Parameters description:**

- *mediaId*: The id of the media.

### Filesystems configuration

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
More details about [Gaufrette bundle](https://github.com/KnpLabs/Gaufrette.git).

//Work in progress
