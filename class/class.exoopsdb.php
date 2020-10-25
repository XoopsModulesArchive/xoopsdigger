<?php
//Class just here for not changing all code and have only one development process
class ExoopsDB
{
    public $db;

    public function __construct()
    {
        $this->db = XoopsDatabaseFactory::getDatabaseConnection();
    }

    public function fetch_row($result)
    {
        return $this->db->fetchRow($result);
    }

    public function fetch_array($result)
    {
        return $this->db->fetchArray($result);
    }

    public function num_rows($result)
    {
        return $this->db->getRowsNum($result);
    }

    public function query($sql, $limit = 0, $start = 0)
    {
        return $this->db->queryF($sql, $limit, $start);
    }

    public function insert_id()
    {
        return $this->db->getInsertId();
    }

    public function prefix($table = '')
    {
        return $this->db->prefix($table);
    }

    public function error()
    {
        return $this->db->errno() . ':' . $this->db->error();
    }
}
