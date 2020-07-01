# CRUDapi - Casual MySQL CRUD functions

- Put files in any folder on the server.   
- Change config file to allow databases & tables.   
- Manage data with HTTP methods.   
- Get JSON format response.   

### Create
```js
Method: POST    
Encoding: UTF-8   
URL: http://127.0.0.1/.../mydatabase/mytable/  

Content-Type: application/json    | Content-Type: application/x-www-form-urlencoded  
Data: '{"id": 1, "name": "John"}' | Data: 'id=1&name=John'  

Curl: curl -X POST -d "id=1&name=John" -H "Content-Type: x-www-form-urlencoded" http://127.0.0.1/.../mydatabase/mytable/  
```

### Read
```js
Method: GET   
URL: http://127.0.0.1/.../mydatabase/mytable/   

Curl: curl http://127.0.0.1/.../mydatabase/mytable/  
```

### Read with condition
```js
Method: GET   
URL: http://127.0.0.1/.../mydatabase/mytable/?attr=value   

Curl: curl http://127.0.0.1/.../mydatabase/mytable/?attr=value    
```

### Update
```js
Method: PUT      
Encoding: UTF-8   
URL: http://127.0.0.1/.../mydatabase/mytable/?attr=value  

Content-Type: application/json    | Content-Type: application/x-www-form-urlencoded  
Data: '{"id": 1, "name": "John"}' | Data: 'id=1&name=John'    

Curl: curl -X PUT -d "id=1&name=John" -H "Content-Type: x-www-form-urlencoded" http://127.0.0.1/.../mydatabase/mytable/?attr=value   
 ```
 
### Delete
```js
Method: DELETE
URL: http://127.0.0.1/api/mydatabase/mytable/?id=1

Curl: curl -X DELETE http://127.0.0.1/.../mydatabase/mytable/?attr=value   
```
