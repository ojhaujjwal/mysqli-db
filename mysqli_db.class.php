<?php
    
namespace library\Mysqli;

class DbManager
{
    /*
    **  @param $mysqli  --- instance of Mysqli Class
    */
    protected $mysqli;


    /*
    **  @param $_instance  --- instance of mysqliDb Class
    */    
    private $_instance=NULL;

    /*
    **  @param  ----    query that was requested at last time when connecting to mysql
    */
    private $last_query="";
    
    
    /*
    **  @function __construct    -- call connect_mysqli for initiating connection to mysql
    **  @param string optional $dbname  -- database name 
    */
    public  function __construct($dbname=NULL){
        
        $this->connect_mysqli($dbname);
        
    }
    /*
    **  @function connect_mysqli    --  initiates connection with mysql
    **  @param string optional $dbname  -- database name
    */
    public function connect_mysqli($hostname,$dbuser,$dbpassword,$dbname=NULL){
        
       if(!isset($this->mysqli)){
            if(is_null($dbname)){
                
                $this->mysqli=new \mysqli($hostname,$dbuser,$dbpassword);
               
            }else{
                
                $this->mysqli=new \mysqli($hostname,$dbuser,$dbpassword,$dbname);
                
            }
            $this->_instance=$this;
            if($this->mysqli->connect_errno){
                throw new \Exception($this->mysqli->connect_errno);
            }
                  
       }
       return $this->mysqli; 
    }

    /*
    **  @function last_error    --  last error occured when querying
    */
    public function last_error(){
        return $this->mysqli->error;
    }

    /*
    **  @function getLastQuery    --  returns query that was requested at last time when connecting to mysql
    */
    public function getLastQuery(){
        return $this->last_query;
    }

    /*
    **  @function query    --  call method query of mysqli class stores query as last query
    */
    public function query($query){
        $this->last_query=$query;
        return $this->mysqli->query($query);
    }

    /*
    **  @function get_instance    --  returns of self
    **  @param string optional $dbname  -- database name
    */
    public function get_instance($dbname=NULL){
        if(!$this->_instance){
            $this->connect_mysqli($dbname);
        }
        return $this->_instance;
    }

    /*
    **  @function get_current_db    --  returns currently connected database
    */
    function get_current_db() {
        $res=$this->mysqli->query("SELECT DATABASE() as db");
        $row=$res->fetch_assoc();
        return $row["db"];
    }

    /*
    **  @function getInsertId    --  returns insert id of last insert transaction
    */
    public function getInsertId(){
        return $this->mysqli->insert_id;
    }


    /*
    **  @function lastError    --  returns error  of last query transaction
    */
    public function lastError(){
        return $this->mysqli->error;
    }

    /*
    **  @function __call    --  when no method is found in current class, it calls method of mysqli class if found in mysqli class
    **  @param string $name  -- method name 
    **  @param array $arguments  -- array of arguments
    **  @thrown BadMethodCallException  -- if no method is found in mysqli class
    */
    public function __call($name,$arguments){
        if(method_exists($this->mysqli,$name)){
            return call_user_func_array(array($this->mysqli,$name),$arguments);
        }
        throw new \BadMethodCallException("Called to undefined method $name!");
    }
    

    /*
    **  @function escape    -- escapes string or elements of array
    **  @param  $var    --  can be array or string  --  the string or array to be escaped
    **  @param bool $recurse_escape --  if set to true and $var is array, escapes all elements of $var array
    */
    public function escape($var,$recurse_escape=TRUE){
       
        if(!is_array($var)){
            $res=$this->mysqli->real_escape_string($var);
        }else{
            $res=array();
            foreach($var as $key=>$value){
                if($recurse_escape){
                    $res[$key]=$this->escape($value,$recurse_escape);
                }else{
                    $res[$key]=$value;
                }
                
            }
        }
        return $res;
    }


    /*
    **  @function insertFromArray   --  inserts new set of data to table by escaping data
    **  @param associative array $data  --   data to be inserted in table 
    */
    public function insertFromArray($table, $data)
    {
        $query_col = array();
        $query_v = array();
        $data=$this->escape($data);    
        foreach($data as $k=>$v)
        {
            $query_col[] = "`" . $k . "`";
            if(is_array($v) and isset($v["type"]) and  $v["type"]=='MYSQL_FUNCTION'){
                
                $query_v[] = $v["value"];
                
            }else{
                $query_v[] = "'$v'";
            }
            
            
        }
        $query = "INSERT INTO " . $table . "(" . implode(", ", $query_col) . ")VALUES(" . implode(", ", $query_v) . ")";
       
        return  $this->query($query);
    }

    public function insertMultiRow($table,$fields,$datas){
        $question=array();
        for($i=0;$i<count($fields);$i++){
            $question[]="?";
        }
        $stmt = $dbh->prepare("INSERT INTO $table (" . implode(", ", $fields) . ") VALUES (" . implode(",",$question) . ")");
        foreach($datas as $data){
            
        }
    }

    /*
    **  @function getWhereConditionFromArray   --  returns where condition
    **  @param associative array $where  --   where condition
    */
    private function getWhereConditionFromArray($where){
        if(empty($where)){
            return '';
        }else{
            $where=$this->escape($where);
            foreach($where as $k=>$v)
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
    private function getWhereCondition($where){
        $where_sql=$this->getWhereConditionFromArray($where);
        if(empty($where_sql)){
            return "";
        }
        return " WHERE ".$where_sql;
    }

    /*
    **  @function updateFromArray   -- updates table using array as arguments
    **  @param string  $table   --  table to be updated 
    **  @param associative array $where  --   where condition 
    */
    public function updateFromArray($table, $data, $where)
    {        
        $query_v = array();
                
        foreach($data as $k=>$v)
        {            
            $k = $this->escape($k);
            if(is_array($v) and isset($v["type"]) and  $v["type"]=='MYSQL_FUNCTION'){
                $query_v[] = "`" . $k .  "`=" .  $this->escape($v['value']) . "";
            }else{
                $query_v[] = "`" . $k .  "`='" . $this->escape($v) . "'";
            }            
            
            
        }
        $where_condition=$this->getWhereCondition($where);
         
        $query = "UPDATE " . $table . " SET " . implode(", ", $query_v) . "$where_condition";
        //echo $query;
        return $this->query($query);
    }

    /*
    **  @function updateFromQuery   -- updates table using query as argument
    */  
    public function updateFromQuery($query){
        return $this->query($query);
    }

    /*
    **  @function selectFromArray   -- select rows of table using array as argument
    **  @param string  $table   --  table to be updated 
    **  @param associative array $where  --   where condition 
    */ 
    private function selectFromArray($table,$fields,$where){
        $where_condition=$this->getWhereCondition($where);
 
        $sql="SELECT ".implode(",",$fields)." FROM $table $where_condition";
        
        return $this->query($sql);
    }

    /*
    **  @function countFromArray    -- counts num of results using array as argument
    **  @param string  $table   --  table to be updated 
    **  @param associative array $where  --   where condition 
    */
    public function countFromArray($table,$where=array()){

        $where_condition=$this->getWhereCondition($where);  
   
        $sql="SELECT count(*) as count FROM $table $where_condition";
        //echo $sql;
        $res=$this->mysqli->query($sql);
        if($res){
            //var_dump($res);
            $row=$res->fetch_assoc();
            return $row["count"];

        }
    }

    /*
    **  @function getRow    --  returns first result of select query
    **  @param string  $res   --  output of "mysql_query function" or "query method of mysqli class"
    */
    private function getRow($res){
        
            if(!$res or $res->num_rows!==1){
                return FALSE;
            }else{
                $row=$res->fetch_assoc();
                
                if(count($row)==1){
                   
                    $value=reset($row);
                    return $value;
                }
                
                return $row;  
                          
            }
           
    }

    /*
    **  @function getRow    --  returns first result of select query taking query as arguments
    **  @param string  $query   --  query to be issued
    */
    public function getRowFromQuery($query){
        
        return $this->getRow($this->query($query));
    }

    /*
    **  @function getRowFromArray    --  returns first result of select query taking array as arguments
    **  @param string  $table   --  table name
    **  @param array fields -- array of fields 
    **  @param associative array optional $where  --   where condition 
    */
    public function getRowFromArray($table,$fields,$where=array()){
        
        return $this->getRow($this->selectFromArray($table,$fields,$where));
    }


    /*
    **  @function getMultiRow    --  returns all rows of select query
    **  @param string  $res   --  output of "mysql_query function" or "query method of mysqli class"
    */
    private function getMultiRow($res){
        if(!$res){
            return FALSE;
        }else{
            $results=array();
            while($row=$res->fetch_assoc()){
                if(count($row)==1){
                    $value=reset($row);
                   
                    $results[]=$value;
                }else{
                    $results[]=$row;        
                }
                        
            }
            return $results;              
        }        
    }

    /*
    **  @function getMultiRowFromQuery    --  returns all rows of select query
    **  @param string  $query   --  query to be issued
    */
    public function getMultiRowFromQuery($query){
        return $this->getMultiRow($this->mysqli->query($query));   
    }

    /*
    **  @function getRowFromArray    --  returns all rows of select query taking array as arguments
    **  @param string  $table   --  table name
    **  @param array fields -- array of fields 
    **  @param associative array optional $where  --   where condition 
    */
    public function getMultiRowFromArray($table,$fields,$where=array()){
        
        return $this->getMultiRow($this->selectFromArray($table,$fields,$where));        
    }


    /*
    **  @function deleteFromArray   --  delete rows of table taking array as arguments
    **  @param string  $table   --  table name
    **  @param associative array optional $where  --   where condition 
    */
    public function deleteFromArray($table,$where=array()){
        $where_condition=$this->getWhereCondition($where);
        $query=  "DELETE FROM $table ".$where_condition." ;";
        return $this->query($query);
    }

    /*
    **  @function __destruct   --  when script execution is finished, this is called
    **  @closes connection with mysql
    */
    public function __destruct(){
        if(isset($this->mysqli)){
            $this->mysqli->close();
        }
        
    }

    /*
    **  @function safeQuery  -- prepares query, bind params and executes
    **  @param string $query --  query to be issued
    **  @param (array or string) bindParams -- bind params
    **  @param (array or string) paramType --  types of bind params
    **  @returns result of query
    */
    public function safeQuery($query,$bindParams,$paramType=NULL)
    {
        if(!is_array($bindParams)){
            $bindParams=array($bindParams);
        }

        $stmt = $this->mysqli->prepare($query);

            if(!is_null($paramType)){
                if(is_array($paramType)){
                    $params[0]=implode("",$paramType);
                }else{
                    $params[0] = $paramType;        
                }
                
            }else{
                $params[0]="";
            }

        foreach ($bindParams as $prop => $val) {
            if(is_null($paramType)){
                $params[0] .= $this->determineType($val);    
            }
            
            array_push($params, $bindParams[$prop]);
        }

        call_user_func_array(array($stmt, 'bind_param'), $this->refValues($params));       
        
        $stmt->execute();
        $query_result=$stmt->get_result();
        $stmt->free_result();
        return $query_result;            
    }

    /*
    **  @function prepareSelect -- calls safeQuery and returns num rows based on @param $numRows
    **  @param string $query --  query to be issued
    **  @param (array or string) bindParams -- bind params
    **  @param (array or string) paramType --  types of bind params
    **  @returns one or more rows based on @param $numRows
    */
    private function prepareSelect($numRows,$query,$bindParams,$paramType){

        $query_result=$this->safeQuery($query,$bindParams,$paramType);
        if($numRows==1){
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
    **  @param (array or string) paramType --  types of bind params
    **  @returns multiple rows 
    */
    public function prepareMultiRow($query,$bindParams,$paramType=NULL){
        return $this->prepareSelect(2,$query,$bindParams,$paramType);
    }

    /*
    **  @function prepareSelect -- calls safeQuery and returns one row 
    **  @param string $query --  query to be issued
    **  @param (array or string) bindParams -- bind params
    **  @param (array or string) paramType --  types of bind params
    **  @returns one row 
    */
    public function prepareRow($query,$bindParams,$paramType=NULL){
        return $this->prepareSelect(1,$query,$bindParams,$paramType);
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

}
