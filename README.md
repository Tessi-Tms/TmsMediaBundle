TmsMediaBundle
==============

Media bundle provides an API to upload and retrieve media.


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

### Filesystems configuration

We are using Gaufrette Bundle to handle the storage layer, below is an example of gaufrette configuration
for a local storage in */web/uploads* directory.

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
I you you want to use another adapter (FTP, GridFS, ...) to store your files, we recommend you to have a look in the [documentation of Gaufrette](https://github.com/KnpLabs/Gaufrette.git).

//Work in progress
