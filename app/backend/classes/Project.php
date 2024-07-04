<?php
namespace App\Backend\Classes;

use App\Backend\Classes\Notifier;
use App\Backend\Classes\Database;

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
            $project_id = $stmt->insert_id; // Вземете последно въведеният ID на проекта
    
            $stmt->close();
            return $project_id; // Връщаме ID на създадения проект
        } else {
            echo "Error: " . $stmt->error;
            $stmt->close();
            return false;
        }
    }

    public function delete($project_id) {
        $stmt = $this->conn->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->bind_param("i", $project_id);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            echo "Error: " . $stmt->error;
            $stmt->close();
            return false;
        }
    }

    public function update($project_id, $fields) {
        foreach ($fields as $field => $value) {
            $query = "UPDATE projects SET $field = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('si', $value, $project_id);
            $stmt->execute();
            $stmt->close();
        }
        return true;
    }
}
?>
