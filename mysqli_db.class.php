<?php
class mysqli_db{
    public $mysqli;
    public $hostname="localhost";
    public $dbuser="root";
    public $dbpassword="XXXXXX";
    
    

    public  function __construct($dbname=NULL){
        $this->connect_mysqli($dbname);
        
    }

    public function connect_mysqli($dbname=NULL){
       if(!isset($this->mysqli)){
            if(is_null($dbname)){
                $this->mysqli=new mysqli($this->hostname,$this->dbuser,$this->dbpassword);
            }else{
                $this->mysqli=new mysqli($this->hostname,$this->dbuser,$this->dbpassword,$dbname);
            }
            
        
            if($this->mysqli->connect_errno){
                throw new Exception($this->mysqli->connect_errno);
            }
                  
       }
       return $this->mysqli; 
    }

    public function last_error(){
        return $this->mysqli->error;
    }

    private function query($query){

        return $this->mysqli->query($query);
    }

    public function get_instance(){
        return $this->mysqli;
    }



    public function getInsertId(){
        return $this->mysqli->insert_id;
    }

    public function lastError(){
        return $this->mysqli->error;
    }

    public function select_db($dbname){
        return $this->mysqli->select_db($dbname);
    }

    public function escape($var,$recurse_escape=TRUE){
        
        if(!is_array($var)){
            $res=$this->mysqli->real_escape_string($var);
            //$res=$var;
        }else{
            $res=array();
            foreach($var as $key=>$value){
                if($recurse_escape){
                    $res[$key]=$this->escape($value);
                }else{
                    $res[$key]=$value;
                }
                
            }
        }
        return $res;
    }

    public function insertFromArray($table, $data,$escape=TRUE)
    {
        $query_col = array();
        $query_v = array();
            if($escape){
                $data=$this->escape($data);
                
            }
            
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

    private function get_where_condition($where,$escape=TRUE){
        if(empty($where)){
            return '';
        }else{
            foreach($where as $k=>$v)
            {                       
                if($escape){
                    $v=$this->escape($v);
                }
                $query_w[] = "`" . $k .  "`='" .  $v . "'";
            }
            return " where 1=1 and (" . implode(" AND ", $query_w) . ")";
        }

    }


    public function updateFromArray($table, $data, $where,$escape=TRUE)
    {        
        $query_v = array();
        
        if($escape){
            $data=$this->escape($data);
            $where=$this->escape($where);
                
        }
        foreach($data as $k=>$v)
        {            
            
            if(is_array($v) and isset($v["type"]) and  $v["type"]=='MYSQL_FUNCTION'){
                $query_v[] = "`" . $k .  "`=" .  $v['value'] . "";
            }else{
                $query_v[] = "`" . $k .  "`='" . $v . "'";
            }            
            
            
        }
        $where_condition=$this->get_where_condition($where);
         
        $query = "UPDATE " . $table . " SET " . implode(", ", $query_v) . "$where_condition";

        return $this->query($query);
    }

    public function updateFromQuery($query){
        return $this->query($query);
    }

    private function selectFromArray($table,$fields,$where){
        $where_condition=$this->get_where_condition($where);   
        $sql="SELECT ".implode(",",$fields)." from $table $where_condition";
        
        return $this->query($sql);
    }

    public function countFromArray($table,$where=array()){

        $where_condition=$this->get_where_condition($where);  
   
        $sql="SELECT count(*) as count from $table $where_condition";
        //echo $sql;
        $res=$this->mysqli->query($sql);
        if($res){
            //var_dump($res);
            $row=$res->fetch_assoc();
            return $row["count"];

        }
    }

    private function getRow($res){
        
            if(!$res or $res->num_rows!==1){
                return FALSE;
            }else{
                $row=$res->fetch_assoc();
                
                if(count($row)==1){
                   
                    return $row[array_keys($row)[0]];
                }
                
                return $row;  
                          
            }
           
    }

    public function getRowFromQuery($query){
        
        return $this->getRow($this->mysqli->query($sql));
    }

    public function getRowFromArray($table,$fields,$where=array()){
        
        return $this->getRow($this->selectFromArray($table,$fields,$where));
    }

    private function getMultiRow($res){
        if(!$res){
            return FALSE;
        }else{
            $results=array();
            while($row=$res->fetch_assoc()){
                if(count($row)==1){
                    $results[]=$row[array_keys($row)[0]];
                }else{
                    $results[]=$row;        
                }
                        
            }
            return $results;              
        }        
    }

    public function getMultiRowFromQuery($query){
        return $this->getMultiRow($this->mysqli->query($query));   
    }

    public function getMultiRowFromArray($table,$fields,$where=array()){
        
        return $this->getMultiRow($this->selectFromArray($table,$fields,$where));        
    }

}
?>
