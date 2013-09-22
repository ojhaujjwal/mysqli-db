mysqli-db
=========

An easy to use mysqli class with php!



Connecting mysql can be done by calling class!

$db=new mysqli("db_name"); // db_name is optional





Escaping a variable is very easy

$escaped = $db->escape($unescaped,true);

  Second parameter(optional, default true) specifies whether all values are to be escaped in case of array!
  
  For example, $user_input=$db->escape($_GET);
  




You can insert data in just a step!

$res=$db->insertFromArray($table,$data);

   $data is an array like this::
   $data=array("column1"=>"value1","column2"=>"value2");
   
  
   
   
Update can be done as follows::

$res=$db->updateFromArray($table, $data, $where);

   $data is an array like this::
   $data=array("column1"=>"value1","column2"=>"value2"); This sets value of column1 as value1,column2 as value2 and so on!
   
You can specify where condition in $where array(optional)

   $where=array("id"=>"1");This update only those rows whose id column has value 1;
   
   
 
   
To get the number of results::   
   
$num=$db->countFromArray($table,$where); 

You can specify where condition in $where array(optional)

   $where=array("id"=>"1");This counts only those rows whose id column has value 1;




To get a row:

$row=$db->getRowFromArray($table,$fields,$where);
You can specify where condition in $where array(optional)

   $where=array("id"=>"1");This returns the row whose id column has value 1;
   
The result $row is an associative array whose key are fields of array $fields and value maps to the resuls from query
But, if you demand only element in $fields array, you get string! 
   
You can also get row in the same format by using query like --
$row=$db->getRowFromQuery("select name from user where  id='1';");




To get multiple rows you can use array format or from query:

$rows=$db->getMultiRowFromArray($table,$fields,$where);
$rows=$db->getMultiRowFromQuery($query);

$where is optional as always

The result, $rows is a numerical array.

But, if you demand only element in $fields array, you get string as elements of the array!

Else you get an associative array as elements of the array!

   



