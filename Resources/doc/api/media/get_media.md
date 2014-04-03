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