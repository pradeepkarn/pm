<?php 
class DB_ctrl
{
    public $db;
    public $conn;
    function  __construct()
    {
        $this->db = new Dbobjects;
        $this->conn = $this->db->conn;
    }
    function start() {
        $this->db->conn->beginTransaction();
    }
    function save() {
        $this->db->conn->commit();
    }
    function undo() {
        $this->db->conn->rollBack();
    }
}
