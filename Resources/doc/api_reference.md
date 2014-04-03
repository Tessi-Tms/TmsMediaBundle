How to use it
-------------

### Definition of the webservices

#### Create a media

**Request**

| Route           | Method | Parameters             | Header
|-----------------|--------|------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /media          | POST   | media={fileContent}     | Content-Type=multipart/form-data

**Response**

This will result to a :

- *201 Created HTTP Status Code* : if a valid media is passed
- *400 Bad Request HTTP Status Code* : if the media is passed twice (i.e media which already exists in the database and in the filesystem)
- *415 Unsupported Media Type HTTP Status Code* : if there is no matched storage provider for the media
- *418 I'am a teapot' HTTP Status Code* : for other media exception types

For a 201 HTTP Response code, you will also get all media informations (in json format) in the response content.

**Parameters description**

- *media* : Contains the file content

**Example of usage**

```curl 
curl -F name=@pathToTheFile http://your_domain/api/media
```

#### Delete a media

**Request**

| Route                 | Method | Parameters         | Header
|-----------------------|--------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /media/{reference}    | DELETE |                    |

**Response**

This will result to a :

- *204 No Content HTTP Status Code* : if a correct reference is passed
- *404 Not Found HTTP Status Code* : if an invalid reference (i.e a reference which does not exist neither in the database or in the filesystem)

**Parameters description**

- *reference* : The unique reference of the media

**Example of usage**

```curl
curl -X DELETE http://your_domain/api/media/reference
```

#### Get a media

**Request**

| Route                 | Method | Parameters         | Header
|-----------------------|--------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------
| /media/{reference}    | GET    |                    |

**Response**

This will result to a :

- *200 OK HTTP Status Code* : if a correct reference is passed
- *404 Not Found HTTP Status Code* : if an invalid reference is passed

**Parameters description**

- *reference* : The unique reference of the media

**Example of usage**

```curl
curl http://your_domain/api/media/reference
```