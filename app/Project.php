<?php
require_once 'Database.php';

class Project {
    private $conn;
    private $name;
    private $description;
    private $collaborators;
    private $initial_requirements;

    public function __construct($db) {
        $this->conn = $db->getConnection();
    }

    public function setProjectDetails($name, $description, $collaborators, $initial_requirements) {
        $this->name = $name;
        $this->description = $description;
        $this->collaborators = $collaborators;
        $this->initial_requirements = $initial_requirements;
    }

    public function create() {
        $stmt = $this->conn->prepare("INSERT INTO projects (name, description, collaborators, initial_requirements) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $this->name, $this->description, $this->collaborators, $this->initial_requirements);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            echo "Error: " . $stmt->error;
            $stmt->close();
            return false;
        }
    }
}
?>
