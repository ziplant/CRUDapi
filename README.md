# CRUDapi - MySQL CRUD functions

### Create
```js
Method: POST    
Encoding: UTF-8   
URL: http://127.0.0.1/.../mydatabase/mytable/  

Content-Type: application/json    | Content-Type: application/x-www-form-urlencoded
Data: '{"id": 1, "name": "John"}' | Data: 'id=1&name=John'  

Curl example: curl -X POST -d 'id=1&name=John' -H 'Content-Type: x-www-form-urlencoded' http://127.0.0.1/.../mydatabase/mytable/  
```

### Read
```js
Method: GET   
URL: http://127.0.0.1/.../mydatabase/mytable/   

Curl example: curl http://127.0.0.1/.../mydatabase/mytable/  
```

### Read with condition
```js
Method: GET   
URL: http://127.0.0.1/.../mydatabase/mytable/?attr=value   

Curl example: curl http://127.0.0.1/.../mydatabase/mytable/?attr=value    
```

### Update
```js
Method: PUT      
Encoding: UTF-8   
URL: http://127.0.0.1/.../mydatabase/mytable/?attr=value  

Content-Type: application/json    | Content-Type: application/x-www-form-urlencoded
Data: '{"id": 1, "name": "John"}' | Data: 'id=1&name=John'    

Curl example: curl -X PUT -d 'id=1&name=John' -H 'Content-Type: x-www-form-urlencoded' http://127.0.0.1/.../mydatabase/mytable/?attr=value   
 ```
 
### Delete
```js
Method: DELETE
URL: http://127.0.0.1/api/mydatabase/mytable/?id=1

Curl example: curl -X DELETE http://127.0.0.1/.../mydatabase/mytable/?attr=value   
```
