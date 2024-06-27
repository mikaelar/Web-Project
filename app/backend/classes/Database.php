<?php

namespace App\Backend\Classes;

class Database {
    private $conn;
    private $servername;
    private $username;
    private $password;
    private $dbname;

    public function __construct($servername = "localhost", $username = "root", $password = "", $dbname = "form") {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;

        $this->conn = new \mysqli($this->servername, $this->username, $this->password, $this->dbname);

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