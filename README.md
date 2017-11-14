# just cURL it

A simple PHP cURL tool to test APIs, RESTful services etc and what-not.



## how to deploy

1. git clone http://github.com/aletzo/curlit.git curlit
2. cd curlit
3. php -S localhost:9000



## how to send headers

You can use the first textarea to optionally send some request headers. E.g. 

```
Content-Type: application/json
```

or 

```
Authorization: Basic BASE64(username:password)
```
