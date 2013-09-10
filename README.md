TmsMediaBundle
==============

The MediaBundle for Symfony2 provides an API to retrieve and upload a media in a specific filesystem.
It supports [Gaufrette](https://github.com/KnpLabs/Gaufrette.git) to handle filesystem abstraction layer and uses [KnpGaufretteBundle]
(https://github.com/KnpLabs/KnpGaufretteBundle.git) to provide a Gaufrette integration in the project.


Installation
------------

To install this bundle please follow the next steps :

First add the dependencies in your `composer.json` file :

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
        "knplabs/gaufrette": "0.2.*@dev",
        "knplabs/knp-gaufrette-bundle": "dev-master",
        "tms/media-bundle": "dev-master"
    },
```

Then install the bundles with the command :

```sh
composer update
```

Enable the bundles in your application kernel :

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        //
        new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
        new Tms\Bundle\MediaBundle\TmsMediaBundle(),
    );
}
```
Now the Bundles are installed

How to use it
-------------

### Definition of the webservices

#### Create a media

**Request**

| Route           | Method | Parameters             | Header
|-----------------|--------|------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /media          | POST   | file={fileContent}     | Content-Type=multipart/form-data

**Response**

This will result to a `200 OK HTTP Status Code ` if a valid media is passed; but passing invalid media will result to a `400 Bad Request HTTP Status Code`

**Parameters description**

- *file* : Contains the file content

**Example of usage**

```curl 
curl -F name=@pathToTheFile http://your_domain/media
```

#### Delete a media

**Request**

| Route                 | Method | Parameters         | Header
|-----------------------|--------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /media/{reference}    | DELETE |                    |

**Response**

This will result to a `204 No Content HTTP Status Code ` if a correct reference is passed; but passing invalid reference will result to a `400 Bad Request HTTP Status Code`

**Parameters description**

- *reference* : The unique reference of the media

**Example of usage**

```curl
curl -X DELETE http://your_domain/media/reference
```

#### Get a media

**Request**

| Route                 | Method | Parameters         | Header
|-----------------------|--------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /media/{reference}    | GET    |                    |

**Response**

This will result to a `200 OK HTTP Status Code ` if a correct reference is passed; but passing invalid reference will result to a `400 Bad Request HTTP Status Code`

**Parameters description**

- *reference* : The unique reference of the media

**Example of usage**

```curl
curl http://your_domain/media/reference
```

### Configure your filesystems

The filesystem abstract layer permits you to develop your application without the need to know where your media will be stored and how. Another advantage of this is the possibility to update your files location without any impact on the code apart from the definition of your filesystem.

#### Example of Gaufrette Filesystem configuration

The following configuration is a local sample configuration for the KnpGaufretteBundle. It will create a filesystem service called `gaufrette.gallery_filesystem` which can be used in the MediaBundle. All the uploaded files will be stored in `/web/uploads` directory.


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
For a complete list of features refer to the [official documentation of GaufretteBundle](https://github.com/KnpLabs/KnpGaufretteBundle.git).

#### Configure your mappings

Pass the Gaufrette service `gaufrette.gallery_filesystem` configured in the previous step to the `storage_provider` property.

**Available rules :**

- **mime_types** : defines an array of valid mime types.
- **max_size** : defines the maximum allowed size of a media.
- **min_size** : defines the minimum allowed size of a media.
- **created_before** : defines if the media was created before this date.
- **created_after** : defines if the media was created after this date.

Notice that the value of *max_size* and *min_size* properties can only be expressed in **KB**, **MB**, **GB**, **TB** and **PB**.

```php

# app/config/config.yml

tms_media:
    storage_mappers:
        image:
            storage_provider: gaufrette.gallery_filesystem
            rules:
                mime_types: ['image/jpg', 'image/png', 'image/jpeg']
                max_size: 5MB
                min_size: 1MB
                created_before: 2014-08-14T12:00:00+0100
                created_after: 2014-07-14T21:00:00+0100
```

//Work in progress
