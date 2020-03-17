<?php

namespace App\Base;

class BaseModel
{

    protected static $tableName = '';

    protected static $primaryKey = '';

    protected $columns = [];

    /**
     * BaseModel constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $column
     * @param $value
     */
    public function setColumnValue($column,$value)
    {
        $this->columns[$column] = $value;
    }

    /**
     * @param $column
     * @return mixed
     */
    public function getColumnValue($column)
    {
        return $this->columns[$column];
    }
    /**
     * Save or update the item data in database
     */
    public function save()
    {
        $query =  "REPLACE INTO " . static::$tableName . " (" . implode(",", array_keys($this->columns)) . ") VALUES(";
        $keys = [];
        foreach ($this->columns as $key => $value) {
            $keys[":".$key] = $value;
        }
        $query .= implode(",", array_keys($keys)).")";
        $db = DbConnection::getInstance();
        $s = $db->getPreparedStatment($query);
        $s->execute($keys);
    }

    /**
     * Delete this item data from database
     */
    public function delete()
    {
        $class = get_called_class();
        $query = "DELETE FROM " . static::$tableName . " WHERE ".static::$primaryKey."=:id LIMIT 1";
        $db = DbConnection::getInstance();
        $s = $db->getPreparedStatment($query);
        $s->execute([':id'=>$this->columns[static::$primaryKey]]);
    }

    /**
     * Create an instance of this Model from the database row
     */
    public function createFromDb($column)
    {
        foreach ($column as $key => $value) {
            $this->columns[$key] = $value;
        }
    }

    /**
     * Get all items
     * Conditions are combined by logical AND
     * @example getAll(array(name=>'Bond',job=>'artist'),'age DESC',0,25) converts to SELECT * FROM TABLE WHERE name='Bond' AND job='artist' ORDER BY age DESC LIMIT 0,25
     *
     * @param array $condition
     * @param null $order
     * @param null $startIndex
     * @param null $count
     * @return array
     */
    public static function getAll($condition=array(),$order=NULL,$startIndex=NULL,$count=NULL)
    {
        $query = "SELECT * FROM " . static::$tableName;
        if(!empty($condition)){
            $query .= " WHERE ";
            foreach ($condition as $key => $value) {
                $query .= $key . "=:".$key." AND ";
            }
        }
        $query = rtrim($query,' AND ');
        if($order){
            $query .= " ORDER BY " . $order;
        }
        if($startIndex !== NULL){
            $query .= " LIMIT " . $startIndex;
            if($count){
                $query .= "," . $count;
            }
        }
        return self::get($query,$condition);
    }

    /**
     * Pass a custom query and condition
     * @example get('SELECT * FROM TABLE WHERE name=:user OR age<:age',array(name=>'Bond',age=>25))
     *
     * @param $query
     * @param array $condition
     * @return array
     */
    public static function get($query,$condition=[])
    {
        $db = DbConnection::getInstance();
        $s = $db->getPreparedStatment($query);
        foreach ($condition as $key => $value) {
            $condition[':'.$key] = $value;
            unset($condition[$key]);
        }
        $s->execute($condition);
        $result = $s->fetchAll(PDO::FETCH_ASSOC);
        $collection = array();
        $className = get_called_class();
        foreach($result as $row){
            $item = new $className();
            $item->createFromDb($row);
            array_push($collection,$item);
        }
        return $collection;
    }

    /**
     * Get a single item
     *
     * @param array $condition
     * @param null $order
     * @param null $startIndex
     * @return mixed
     */
    public static function getOne($condition=array(),$order=NULL,$startIndex=NULL)
    {
        $query = "SELECT * FROM " . static::$tableName;
        if(!empty($condition)){
            $query .= " WHERE ";
            foreach ($condition as $key => $value) {
                $query .= $key . "=:".$key." AND ";
            }
        }
        $query = rtrim($query,' AND ');
        if($order){
            $query .= " ORDER BY " . $order;
        }
        if($startIndex !== NULL){
            $query .= " LIMIT " . $startIndex . ",1";
        }
        $db = DbConnection::getInstance();
        $s = $db->getPreparedStatment($query);
        foreach ($condition as $key => $value) {
            $condition[':'.$key] = $value;
            unset($condition[$key]);
        }
        $s->execute($condition);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        $className = get_called_class();
        $item = new $className();
        $item->createFromDb($row);
        return $item;
    }

    /**
     * Get an item by the primarykey
     *
     * @param $value
     * @return mixed
     */
    public static function getByPrimaryKey($value)
    {
        $condition = [];
        $condition[static::$primaryKey] = $value;
        return self::getOne($condition);
    }

    /**
     * Get the number of items
     *
     * @param array $condition
     * @return mixed
     */
    public static function getCount($condition=array())
    {
        $query = "SELECT COUNT(*) FROM " . static::$tableName;
        if(!empty($condition)){
            $query .= " WHERE ";
            foreach ($condition as $key => $value) {
                $query .= $key . "=:".$key." AND ";
            }
        }
        $query = rtrim($query,' AND ');
        $db = DbConnection::getInstance();
        $s = $db->getPreparedStatement($query);
        foreach ($condition as $key => $value) {
            $condition[':'.$key] = $value;
            unset($condition[$key]);
        }
        $s->execute($condition);
        $countArr = $s->fetch();
        return $countArr[0];
    }

}