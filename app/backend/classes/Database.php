<?php

namespace App\Backend\Classes;

class Database {
    private const DEFAULT_SERVERNAME = "localhost";
    private const DEFAULT_USERNAME = "root";
    private const DEFAULT_PASSWORD = "";
    private const DEFAULT_DB_NAME = "form-final-final";

    private $conn;

    public function __construct() {

        $this->conn = new \mysqli(self::DEFAULT_SERVERNAME, self::DEFAULT_USERNAME, self::DEFAULT_PASSWORD, self::DEFAULT_DB_NAME);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function __destruct() {
        $this->conn->close();
    }
}

?>