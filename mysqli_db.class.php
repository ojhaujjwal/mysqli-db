<?php
namespace Mysqli;

class DbManager
{
    /*
    **  @param $mysqli  --- instance of Mysqli Class
    */
    protected $mysqli;


    /*
    **  @param $instance  --- instance of mysqliDb Class
    */    
    private $instance = NULL;

    /*
    **  @param  ----    query that was requested at last time when connecting to mysql
    */
    private $last_query = "";
    
    
    /*
    **  @function __construct    -- call connect for initiating connection to mysql
    **  @param string  $hostname  -- hostname  
    **  @param string  $dbUser  -- database user name 
    **  @param string  $dbPassword  -- database password 
    **  @param string optional $dbname  -- database name 
    */
    public  function __construct($hostname, $dbUser, $dbPassword, $dbname = NULL)
    {
        $this->connect($hostname, $dbUser, $dbPassword, $dbname);        
    }
    /*
    **  @function connect    --  initiates connection with mysql
    **  @param string  $hostname  -- hostname  
    **  @param string  $dbUser  -- database user name 
    **  @param string  $dbPassword  -- database password 
    **  @param string optional $dbname  -- database name 
    */
    public function connect($hostname, $dbUser, $dbPassword, $dbname = NULL)
    {
       if (!isset($this->mysqli)) {
            if (is_null($dbname)) {
                $this->mysqli = new \mysqli($hostname, $dbUser, $dbPassword);
            } else {
                $this->mysqli = new \mysqli($hostname, $dbUser, $dbPassword, $dbname);
            }
            $this->instance=$this;
            if($this->mysqli->connect_errno){
                throw new \Exception($this->mysqli->connect_errno);
            }
       }
    }

    /*
    **  @function getLastQuery    --  returns query that was requested at last time when connecting to mysql
    */
    public function getLastQuery()
    {
        return $this->last_query;
    }

    public function setLastQuery($query)
    {
        $this->last_query=$query;
    }

    /*
    **  @function query    --  call method query of mysqli class stores query as last query
    **  @param string query  -- query 
    */
    public function query($query)
    {
        $this->setLastQuery($query);
        return $this->mysqli->query($query);
    }

    /*
    **  @function getInstance    --  returns instance of self
    **  @param string optional $dbname  -- database name
    */
    public function getInstance($dbname=NULL)
    {
        if (!$this->instance) {
            $this->connect($dbname);
        }
        return $this->instance;
    }

    /*
    **  @function getCurrentDb    --  returns currently connected database
    */
    function getCurrentDb() 
    {
        return $this->getRowFromQuery("SELECT DATABASE() as db");
    }

    /*
    **  @function getInsertId    --  returns insert id of last insert transaction
    */
    public function getInsertId()
    {
        return $this->mysqli->insert_id;
    }


    /*
    **  @function lastError    --  returns error  of last query transaction
    */
    public function lastError()
    {
        return $this->mysqli->error;
    }

    /*
    **  @function __call    --  when no method is found in current class, it calls method of mysqli class if found in mysqli class
    **  @param string $name  -- method name 
    **  @param array $arguments  -- array of arguments
    **  @thrown BadMethodCallException  -- if no method is found in mysqli class
    */
    public function __call($name, $arguments)
    {
        if(method_exists($this->mysqli, $name)){
            return call_user_func_array(array($this->mysqli,$name), $arguments);
        }
        throw new \BadMethodCallException("Called to undefined method $name!");
    }
    

    /*
    **  @function __get -- when no property is found in current class, it returns property mysqli class
    **  @param string $name  -- property name 
    **  @thrown exception if property is not found in mysqli Class
    */
    public function __get($name)
    {
        if(property_exists(array($this->mysqli, $name))){
            return $this->mysqli->$name;
        }
        throw new \Exception("Called to undefined property $name!");
    }

    /*
    **  @function escape    -- escapes string or elements of array
    **  @param  $var    --  can be array or string  --  the string or array to be escaped
    **  @param bool $recurse_escape --  if set to true and $var is array, escapes all elements of $var array
    */
    public function escape($var, $recurse_escape=TRUE)
    {
       
        if (!is_array($var)) {
            $res = $this->mysqli->real_escape_string($var);
        } else {
            $res = array();
            foreach ($var as $key=>$value) {
                if ($recurse_escape) {
                    $res[$key] = $this->escape($value, $recurse_escape);
                } else {
                    $res[$key] = $value;
                }
                
            }
        }
        return $res;
    }


    /*
    **  @function insert   --  inserts new set of data to table by escaping data
    **  @param associative array $data  --   data to be inserted in table 
    */
    public function insert($table, $data)
    {
        $query_col = array();
        $query_v = array();
        $data = $this->escape($data);    
        foreach ($data as $k => $v)
        {
            $query_col[] = "`" . $k . "`";
            if (is_array($v) && isset($v["type"]) &&  $v["type"] == 'MYSQL_FUNCTION') {
                $query_v[] = $v["value"];
            } else {
                $query_v[] = "'$v'";
            }
            
            
        }
        $query = "INSERT INTO " . $table . "(" . implode(", ", $query_col) . ")VALUES(" . implode(", ", $query_v) . ")";
       
        return  $this->query($query);
    }

    /*
    **  @function getPreparingWhereConditionFromArray   --  returns where condition for preparing
    **  @param associative array $where  --   where condition
    */
    private function getPreparingWhereConditionFromArray($where)
    {

        $where = $this->escape($where);
        foreach ($where as $k => $v)
        {                       
            $query_w[] = "`" . $k .  "`=?";
        }
        return implode(" AND ", $query_w);
                
    }

    private function getPreparingWhereCondition($where)
    {
        if (empty($where)) {
            return "";
        } else {
            return " WHERE ".$this->getPreparingWhereConditionFromArray($where)." ";
        }
    }

    /*
    **  @function getWhereConditionFromArray   --  returns where condition
    **  @param associative array $where  --   where condition
    */
    private function getWhereConditionFromArray($where)
    {
        if (empty($where)) {
            return '';
        } else {
            $where = $this->escape($where);
            foreach ($where as $k => $v)
            {                       
                $query_w[] = "`" . $k .  "`='" .  $v . "'";
            }
            return implode(" AND ", $query_w);
        }        
    }

    /*
    **  @function getWhereCondition   --  returns where condition in SQL format using above method getWhereConditionFromArray
    **  @param associative array $where  --   where condition
    */
    private function getWhereCondition($where)
    {
        $where_sql = $this->getWhereConditionFromArray($where);
        if (empty($where_sql)) {
            return "";
        }
        return " WHERE ".$where_sql;
    }

    /*
    **  @function updateFromArray   -- updates table using array as arguments
    **  @old way to update, uses escaping method
    **  @param string  $table   --  table to be updated 
    **  @param associative array $where  --   where condition 
    */
    public function updateFromArrayOld($table, $data, $where)
    {        
        $query_v = array();
                
        foreach ($data as $k => $v)
        {            
            $k = $this->escape($k);
            if (is_array($v) and isset($v["type"]) and  $v["type"] == 'MYSQL_FUNCTION') {
                $query_v[] = "`" . $k .  "`=" .  $this->escape($v['value']) . "";
            } else {
                $query_v[] = "`" . $k .  "`='" . $this->escape($v) . "'";
            }            
            
        }
        $where_condition = $this->getWhereCondition($where);
        $query = "UPDATE " . $table . " SET " . implode(", ", $query_v) . "$where_condition";
        return $this->query($query);
    }


    /*
    **  @function update   -- updates table using array as arguments
    **  @new way to update, uses prepared statements(i.e. safeQuery method)
    **  @param string  $table   --  table to be updated 
    **  @param associative array $where  --   where condition 
    */
    public function update($table, $data, $where)
    {        
        $query_v = array();
                
        foreach($data as $k=>$v)
        {            
            if (is_array($v) and isset($v["type"]) and  $v["type"] =='MYSQL_FUNCTION') {
                $query_v[] = "`" . $k .  "`=" .  $this->escape($v['value']);
                unset($data[$k]);
            } else {
                $query_v[] = "`" . $k .  "`=? ";
            }            
            
            
        }
        $where_condition = $this->getPreparingWhereCondition($where);

        $query = "UPDATE " . $table . " SET " . implode(", ", $query_v) . "$where_condition";
        return $this->safeQuery($query, array_merge(array_values($data), array_values($where)));
    }

    /*
    **  @function updateFromQuery   -- updates table using query as argument
    */  
    public function updateFromQuery($query)
    {
        return $this->query($query);
    }

    /*
    **  @function selectFromArrayOld   -- select rows of table using array as argument, uses escaping in where condition
    **  @param string  $table   --  table to be updated 
    **  @param associative array $where  --   where condition 
    */ 
    private function selectFromArrayOld($table, $fields, $where)
    {
        $where_condition = $this->getWhereCondition($where);
 
        $sql = "SELECT ".implode(",", $fields)." FROM $table $where_condition";
        
        return $this->query($sql);
    }

    /*
    **  @function selectFromArray   -- select rows of table using array as argument, use safeQuery method(preparing)
    **  @param string  $table   --  table to be updated 
    **  @param associative array $where  --   where condition 
    */ 
    private function selectFromArray($table, $fields, $where)
    {
        $query="SELECT ".implode(",", $fields)." FROM $table ";
        if (empty($where)) {
            return $this->query($query);
        }

        $where_condition = $this->getPreparingWhereCondition($where);
 
        $query .= $where_condition;
        
        return $this->safeQuery($query, array_values($where));
    }

    /*
    **  @function countRows    -- counts num of results using array as argument, use safeQuery method(preparing)
    **  @param string  $table   --  table to be updated 
    **  @param associative array $where  --   where condition 
    */
    public function countRows($table, $where=array())
    {
        return $this->value($table, "count(*)", $where);
    }

    /*
    **  @function countFromArray    -- counts num of results using array as argument, uses escaping in where condition
    **  @unsafe and unprepared  -- slightly unsecure than the above method
    **  @param string  $table   --  table to be updated 
    **  @param associative array $where  --   where condition 
    */
    public function countFromArrayOld($table, $where=array())
    {

        $where_condition = $this->getWhereCondition($where);  
   
        $query = "SELECT count(*) as count FROM $table $where_condition";
        return $this->getRowFromQuery($query);
    }

    /*
    **  @function getRow    --  returns first result of select query
    **  @param string  $res   --  output of "mysql_query function" or "query method of mysqli class"
    */
    private function getRow($res)
    {
        
        if ($res && $res->num_rows > 1) {
            throw new \BadMethodCallException("Query returns more than row!");
        } else {  
            $rows = $this->getMultiRow($res);
            return reset($rows);            
        }
           
    }

    /*
    **  @function getRow    --  returns first result of select query taking query as arguments
    **  @param string  $query   --  query to be issued
    */
    public function getRowFromQuery($query)
    {
        
        return $this->getRow($this->query($query));
    }

    /*
    **  @function row    --  returns first result of select query taking array as arguments
    **  @param string  $table   --  table name
    **  @param array fields -- array of fields 
    **  @param associative array optional $where  --   where condition 
    */
    public function row($table,array $fields, $where=array())
    {
        
        return $this->getRow($this->selectFromArray($table, $fields, $where));
    }

    public function value($table, $field, $where=array())
    {
        $row = $this->row($table, array($field), $where);
        $value = reset($row);
        return $value;
    }

    public function column($table, $field, $where=array())
    {
        $rows = $this->multiRows($table, array($field), $where);
        return $this->convertRowsToColumn($rows);
    }

    public function valueFromQuery($query)
    {
        $row=$this->getRowFromQuery($query);
        return reset($row);
    }

    public function columnFromQuery($query)
    {
        $rows = $this->getMultiRowFromQuery($query);
        return $this->convertRowsToColumn($rows);
    }

    private function convertRowsToColumn($rows)
    {
        $results=array();
        foreach($rows as $row){
            $results[]=reset($row);
        }
        return $results;        
    }


    /*
    **  @function getMultiRow    --  returns all rows of select query
    **  @param string  $res   --  output of "mysql_query function" or "query method of mysqli class"
    */
    private function getMultiRow($res)
    {
        if(!$res){
            return array();
        }else{
            $results=array();
            while($row=$res->fetch_assoc()){
                $results[]=$row;                                
            }
            return $results;              
        }        
    }

    /*
    **  @function getMultiRowFromQuery    --  returns all rows of select query
    **  @param string  $query   --  query to be issued
    */
    public function getMultiRowFromQuery($query)
    {
        return $this->getMultiRow($this->mysqli->query($query));   
    }

    /*
    **  @function multiRows    --  returns all rows of select query taking array as arguments
    **  @param string  $table   --  table name
    **  @param array fields -- array of fields 
    **  @param associative array optional $where  --   where condition 
    */
    public function multiRows($table,array $fields, $where=array())
    {
        return $this->getMultiRow($this->selectFromArray($table, $fields, $where));        
    }


    /*
    **  @function delete   --  delete rows of table taking array as arguments
    **  @param string  $table   --  table name
    **  @param associative array optional $where  --   where condition 
    */
    public function delete($table, $where=array())
    {
        if (empty($where)) {
            return $this->query($query);
        }
        $where_condition = $this->getPreparingWhereCondition($where);
        $query = "DELETE FROM $table ".$where_condition;
        return $this->safeQuery($query, array_values($where));
    }

    /*
    **  @function __destruct   --  when script execution is finished, this is called
    **  @closes connection with mysql
    */
    public function __destruct()
    {
        if(isset($this->mysqli)){
            $this->mysqli->close();
        }
        
    }

    /*
    **  @function safeQuery  -- prepares query, bind params and executes
    **  @param string $query --  query to be issued
    **  @param (array or string) bindParams -- bind params
    **  @param (array or string) (optional) paramType --  types of bind params
    **  @returns result of query
    */
    public function safeQuery($query, $bindParams, $paramType=NULL)
    {
        $this->setLastQuery($query);       
        if (!is_array($bindParams)) {
            $bindParams = array($bindParams);
        }
        $stmt = $this->mysqli->prepare($query);
        if ($this->isArrayAssoc($bindParams)) {
            foreach ($bindParams as $key => $value) {
                $stmt->bind_param($key, $value);
            }
        } else {
           if (!is_null($paramType)) {
                if (is_array($paramType)) {
                    $params[0] = implode("", $paramType);
                } else {
                    $params[0] = $paramType;        
                }
                
            } else {
                $params[0]="";
            }

            foreach ($bindParams as $prop => $val) {
                if (is_null($paramType)) {
                    $params[0] .= $this->determineType($val);    
                }
            
                array_push($params, $bindParams[$prop]);
            }
           call_user_func_array(array($stmt, 'bind_param'), $this->refValues($params));             
        }
      
        
        if (!$stmt->execute()) {
            return FALSE;
        }
        if (stripos($query, "SELECT") !== FALSE) {
            $return_value = $stmt->get_result();             
        } else {
            $return_value = TRUE;
        }
        $stmt->free_result();
        $stmt->close();
        return $return_value;
           
    }

    /*
    **  @function prepareSelect -- calls safeQuery and returns num rows based on @param $numRows
    **  @param string $query --  query to be issued
    **  @param (array or string) bindParams -- bind params
    **  @param (array or string) (optional) paramType --  types of bind params
    **  @returns one or more rows based on @param $numRows
    */
    private function prepareSelect($numRows, $query, $bindParams, $paramType)
    {
       
        $query_result = $this->safeQuery($query, $bindParams, $paramType);
        if ($numRows == 1) {
            $results = $this->getRow($query_result);    
        }else{
            $results = $this->getMultiRow($query_result);    
        }
        return $results;        
    }

    /*
    **  @function prepareSelect -- calls safeQuery and returns multiple rows 
    **  @param string $query --  query to be issued
    **  @param (array or string) bindParams -- bind params
    **  @param (array or string) (optional) paramType --  types of bind params
    **  @returns multiple rows 
    */
    public function prepareMultiRow($query, $bindParams, $paramType=NULL)
    {
        return $this->prepareSelect(2, $query, $bindParams, $paramType);
    }

    /*
    **  @function prepareSelect -- calls safeQuery and returns one row 
    **  @param string $query --  query to be issued
    **  @param (array or string) bindParams -- bind params
    **  @param (array or string) (optional) paramType --  types of bind params
    **  @returns one row 
    */
    public function prepareRow($query, $bindParams, $paramType=NULL)
    {
        return $this->prepareSelect(1, $query, $bindParams, $paramType);
    }

    /*
    **  @function prepareValue -- calls safeQuery and returns one value 
    **  @param string $query --  query to be issued
    **  @param (array or string) bindParams -- bind params
    **  @param (array or string) (optional) paramType --  types of bind params
    **  @returns a value 
    */
    public function prepareValue($query, $bindParams, $paramType=NULL)
    {
        $row = $this->prepareRow($query, $bindParams, $paramType);
        return reset($row);
    }

    /*
    **  @function prepareSelect -- calls safeQuery and returns an array of column
    **  @param string $query --  query to be issued
    **  @param (array or string) bindParams -- bind params
    **  @param (array or string) (optional) paramType --  types of bind params
    **  @returns array of values 
    */
    public function prepareColumn($query, $bindParams, $paramType=NULL)
    {
        return $this->convertRowsToColumn($this->prepareRow($query, $bindParams, $paramType=NULL));
    }

    /*
    **  @function determineType -- determines type of bind params to prepared query
    **  @param string $item --  string whose type is to be determined
    **  @copied from from https://github.com/ajillion/PHP-MySQLi-Database-Class
    */
    protected function determineType($item)
    {
        switch (gettype($item)) {
            case 'NULL':
            case 'string':
                return 's';
                break;

            case 'integer':
                return 'i';
                break;

            case 'blob':
                return 'b';
                break;

            case 'double':
                return 'd';
                break;
        }
        return '';
    }


    /**
    **  @function refValues
    **  @copied from from https://github.com/ajillion/PHP-MySQLi-Database-Class
     *  @param array $arr
     *  @return array
     */
    protected function refValues($arr)
    {
        //Reference is required for PHP 5.3+
        if (strnatcmp(phpversion(), '5.3') >= 0) {
            $refs = array();
            foreach ($arr as $key => $value) {
                $refs[$key] = & $arr[$key];
            }
            return $refs;
        }
        return $arr;
    }


    /*
    **  @function isArrayAssoc  --  used to check is array is associative
    **  @returns true if array is associative, else not
    */
    private function isArrayAssoc($array){
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

}
