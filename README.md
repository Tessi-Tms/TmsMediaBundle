TmsMediaBundle
==============

The MediaBundle for Symfony2 provides an API to retrieve and upload a media in a specific filesystem.

Features included
-----------------

- Support [Gaufrette](https://github.com/KnpLabs/Gaufrette.git) to handle the filesystem storage layer


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
| /media          | POST   | file={fileContent}    | Content-Type=multipart/form-data

**Parameters description:**

- *file* : Contains the file content.

#### Delete a media

| Route                 | Method | Parameters         | Header
|-----------------------|--------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /media/{id}           | DELETE |                    |

**Parameters description:**

- *id*: The id of the media.

#### Get a media

| Route                 | Method | Parameters         | Header
|-----------------------|--------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /media/{id}           | GET    |                    |

**Parameters description:**

- *id*: The id of the media.

### Configure your filesystems


The filesystem abstract layer permits you to develop your application without the need to know where your media will be stored and how. Another advantage of this is the possibility to update your files location without any impact on the code apart from the definition of your filesystem.

#### Example of configuration

The following configuration is a local sample configuration for the KnpGaufretteBundle. It will create a filesystem service called `gaufrette_gallery_filesystem` which can be used in the MediaBundle. All the uploaded files will be stored in `/web/uploads` directory.


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

For a complete list of features refer to the [official documentation](https://github.com/KnpLabs/Gaufrette.git).

//Work in progress
