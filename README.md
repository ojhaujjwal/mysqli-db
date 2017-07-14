mysqli-db
=========

An easy to use mysqli class with php!

## Deprecated
This library is not maintainted anymore. There are a lot of much better libraries which you can use:

* [paragonie/easydb](https://github.com/paragonie/easydb) - Closest alternative
* [doctrine/dbal](https://github.com/doctrine/dbal) - Powerful database abstraction layer of Doctrine
* [zendframework/zend-db](https://github.com/zendframework/zend-db) - Database abstraction layer, SQL abstraction of Zend
* [illuminate/database](https://github.com/illuminate/database)- Database component of Laravel with an expressive query builder

## Documentation
Connecting mysql can be done by calling class!
```php
$db = new Mysqli\DbManager("hostname", "dbuser", "dbpassword", "db_name"); 
//db_name is optional
```



### Escaping a variable
Escaping a variable is as easy as
```php
$escaped = $db->escape($unescaped, true);
//Second parameter(optional, default true) specifies whether all elements are to be escaped in case of array!
For example, $user_input = $db->escape($_GET);
``` 



### Inserting Data
You can insert data in just a step!
```php
$res = $db->insert($table, $data);
//$data is an array like this::
//$data = array("column1" => "value1", "column2" => "value2");
```    
  
   
### Updating Data   
Update can be done as follows::
```php
$res = $db->update($table, $data, $where);
//$data is an array like this::
//$data = array("column1" => "value1", "column2" => "value2"); 
```
This sets value of column1 as value1,column2 as value2 and so on!


You can specify where condition in `$where` array(optional)
```php
$where = array("id"=>"1");//This update only those rows whose id column has value 1;
```       
   
 
### Counting Rows    
To get the number of results::   
```php   
$num = $db->countRows($table, $where); 
```  
You can specify where condition in `$where` array(optional)
```php  
$where = array("id"=>"1");//This counts only those rows whose id column has value 1;
``` 


### Fetching a Single Row  
To get a row:
```php
$row = $db->row($table, $fields, $where);
```  
You can specify where condition in `$where` array(optional)
```php
$where = array("id"=>"1");//This returns the row whose id column has value 1;
```      
The result `$row` is an associative array whose key are fields of array `$fields` and value maps to the resuls from query

   
You can also get row in the same format by using query like --
```php 
$row = $db->getRowFromQuery("select * from user where  id='1';");
```  

### Fetching a single value
To fetch a single value:
```php
$value = $db->value($table, $field_name, $where);
// for example, $user_name=$db->value("user", "name", array("id"=>"10"));
```


### Fetching Multiple Rows 
To get multiple rows you can use array format or from query:
```php 
$rows = $db->multiRows($table, $fields, $where);// $where is optional as always
$rows = $db->getMultiRowFromQuery($query);
```  
The result, `$rows` is a numerical array.

### Fetching a single Column
To fetch a single column:
```php
$value = $db->column($table, $field_name, $where);
// for example, $user_name_list = $db->column("user", "name");
```



## Prepared Statements
The above methods using query such as `getRowFromQuery` and `getMultiRowFromQuery` are slightly insecure. So a new method, `safeQuery` has been added !

```php 
$result = $db->safeQuery($query, $bindParams, $paramType);// $paramType is optional!
//If third parameter is not provided, the class automatically identifies the param type!
``` 

For example, to update a table, posts: 
```php
$result = $db->safeQuery("update posts set post_text=?", $post_text); 
```

In case of binding multiple variables, second parameter, `$bindParams` should be an array.

For example, to update a user`s posts from table, posts: 
```php
$res = $db->safeQuery("update posts set post_text=? where user_id=? ", array($post_text, $user_id), "bi");
// the third param can be array("b","i") or "bi";
```


The above only returns result and is not suitable for select statements, but only for insert, update, delete etc.


### Prepared Select  Statements

For prepared select statements, use `prepareRow` or `prepareMultiRow`.


##### prepareRow
 
This is used to fetch a row and returns result similiar to row!

For example to get user`s data from table user!
```php
$user_data = $db->prepareRow("select user_name,date_of_birth from user where user_id=?", $user_id, "i");
print_r($user_data);
```



##### prepareMultiRow
 
This is used to fetch multiple rows and returns result similiar to `multiRow`!

For example to get list of user`s posts from table post!
```php
$posts = $db->prepareMultiRow("select * from post where user_id=?", $user_id, "i");
print_r($posts); // will print array
```



##### prepareValue
 
This is used to get value from prepared query!

For example to get user`s name from user table!
```php
$name = $db->prepareValue("select name from user where user_id=?", $user_id, "i");
echo $name; // will print name
```

##### prepareColumn
 
This is used to get a column from prepared query!

For example to get list of user`s post_text from post table!
```php
$post_text = $db->prepareColumn("select post_text from post where user_id=?", $user_id, "i");
print_r($post_text); // will print array
```


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/ojhaujjwal/mysqli-db/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

