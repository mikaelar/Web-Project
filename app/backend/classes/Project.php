<?php
namespace App\Backend\Classes;

use App\Backend\Classes\Database;

class Project {
    //private $conn;
    private $name;
    private $description;
    private $created_at;
    private $author;

    public function __construct($name, $description, $created_at, $author) {
        $this->name = $name;
        $this->description = $description;
        $this->created_at = $created_at;
        $this->author = $author;
    }

    public function create($db) {
        $query = "INSERT INTO projects (name, description, author, created_at) VALUES (?, ?, ?, ?)";
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bind_param("ssss", $this->name, $this->description, $this->author, $this->created_at);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            echo "Error: " . $stmt->error;
            $stmt->close();
            return false;
        }
    }

    // add collaborator, remove collaborator funcs for a project - NB - user has to be author to add others

    public function delete($db, $project_id) {
        $stmt = $db->getConnection()->prepare("DELETE FROM projects WHERE id = ?");
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

    public function update($db, $project_id, $fields) {
        foreach ($fields as $field => $value) {
            $query = "UPDATE projects SET $field = ? WHERE id = ?";
            $stmt = $db->getConnection()->prepare($query);
            $stmt->bind_param('si', $value, $project_id);
            $stmt->execute();
            $stmt->close();
        }
        return true;
    }
}
?>
