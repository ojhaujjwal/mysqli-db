mysqli-db
=========

An easy to use mysqli class with php!



Connecting mysql can be done by calling class!
```php
$db=new \library\Mysqli\DbManager("hostname","dbuser","dbpassword","db_name"); //db_name is optional
```



### Escaping a variable
Escaping a variable is as easy as
```php
$escaped = $db->escape($unescaped,true);
//Second parameter(optional, default true) specifies whether all elements are to be escaped in case of array!
For example, $user_input=$db->escape($_GET);
``` 



### Inserting Data
You can insert data in just a step!
```php
$res=$db->insertFromArray($table,$data);
//$data is an array like this::
//$data=array("column1"=>"value1","column2"=>"value2");
```    
  
   
### Updating Data   
Update can be done as follows::
```php
$res=$db->updateFromArray($table, $data, $where);
//$data is an array like this::
//$data=array("column1"=>"value1","column2"=>"value2"); 
```
This sets value of column1 as value1,column2 as value2 and so on!


You can specify where condition in $where array(optional)
```php
$where=array("id"=>"1");This update only those rows whose id column has value 1;
```       
   
 
### Counting Rows    
To get the number of results::   
```php   
$num=$db->countFromArray($table,$where); 
```  
You can specify where condition in $where array(optional)
```php  
$where=array("id"=>"1");This counts only those rows whose id column has value 1;
``` 


### Fetching a Single Row  
To get a row:
```php
$row=$db->getRowFromArray($table,$fields,$where);
```  
You can specify where condition in $where array(optional)
```php
$where=array("id"=>"1");This returns the row whose id column has value 1;
```      
The result $row is an associative array whose key are fields of array $fields and value maps to the resuls from query
But, if you demand only element in $fields array, you get string! 
   
You can also get row in the same format by using query like --
```php 
$row=$db->getRowFromQuery("select name from user where  id='1';");
```  


### Fetching Multiple Rows 
To get multiple rows you can use array format or from query:
```php 
$rows=$db->getMultiRowFromArray($table,$fields,$where);// $where is optional as always
$rows=$db->getMultiRowFromQuery($query);
```  
The result, $rows is a numerical array.

If you demand only element in $fields array, you get string as elements of the array!

Else you get an associative array as elements of the array!


### Prepared Statements
The above methods using query such as getRowFromQuery and getMultiRowFromQuery are slightly insecure. So a new method, safeQuery has been added !

```php 
$result=$db->safeQuery($query,$bindParams,$paramType);// $paramType is optional!
//If third parameter is not provided, the class automatically identifies the param type!
``` 

For example, to update a table, posts: 
```php
$result=$db->safeQuery("update posts set post_text=?",$post_text); 
```

In case of binding multiple variables, second parameter, $bindParams should be an array.

For example, to update a user`s posts from table, posts: 
```php
$res=$db->safeQuery("update posts set post_text=? where user_id=? ",array($post_text,$user_id),"bi");
// the third param can be array("b","i") or "bi";
```


The above only returns result and is not suitable for select statements, but only for insert, update, delete etc.


### Prepared Select  Statements

For prepared select statements, use <span>prepareRow</span> or <span>prepareMultiRow</span>.


### prepareRow
 
This is used to fetch a row and returns result similiar to getRowFromArray!

For example to get user`s name from table user!
```php
$user_name=$db->prepareRow("select user_name from user where user_id=?",$user_id,"i");
echo $user_name; // will print user name like David Beckham
```



### prepareMultiRow
 
This is used to fetch multiple rows and returns result similiar to getMultiRowFromArray!

For example to get list of user`s posts from table post!
```php
$posts=$db->prepareRow("select * from post where user_id=?",$user_id,"i");
print_r($posts); // will print array
```
