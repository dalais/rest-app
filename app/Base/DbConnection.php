<?php

namespace App\Base;

class DbConnection
{
    /**
     * @var DbConnection
     */
    private static $instance;

    private function __construct()
    {
        $config = config('db')['details'];
        try {
            $this->_instance = new \PDO($config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['db_name'],
                $config['db_user'],
                $config['db_pass'],
                [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]
            );
            $this->_instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo "Error connection:" . $e->getCode(). ' - ' . $e->getMessage();
            die();
        }
    }

    /**
     * @return DbConnection
     */
    public static function getInstance(): DbConnection
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }
}