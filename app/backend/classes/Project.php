<?php
namespace App\Backend\Classes;

use App\Backend\Classes\Notifier;
use App\Backend\Classes\Database;

class Project {
    //private $conn;
    private $id;
    private $name;
    private $description;
    private $created_at;
    private $author;
    private $collaborators;

    public function __construct($name, $description, $created_at, $author) {
        $this->name = $name;
        $this->description = $description;
        $this->created_at = $created_at;
        $this->author = $author;
        $this->id = null;
        $this->collaborators = null;
    }

    public function create($db) {
        $query = "SELECT * FROM projects WHERE name = ? AND author = ?";
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bind_param("ss", $this->name, $this->author);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $stmt->free_result();

            $query = "INSERT INTO projects (name, description, author, created_at) VALUES (?, ?, ?, ?)";
            $stmt = $db->getConnection()->prepare($query);
            $stmt->bind_param("ssss", $this->name, $this->description, $this->author, $this->created_at);
    
            if ($stmt->execute()) {
                $this->id = $stmt->insert_id;
                $stmt->close();
                return true;
            } else {
                echo "Error: " . $stmt->error;
                $stmt->close();
                return false;
            }
        } else {
            echo "<pre>";
            echo "Авторът $this->author вече е създал проект със заглавие $this->name";
            echo "</pre>";
        }

        $stmt->close();
        return false;
    }

    public function retrieveID($db) {
        $query = "SELECT id FROM projects WHERE name = ? AND author = ?";
        $stmt = $db->getConnection()->prepare($query);
        if ($stmt === false) {
            echo "Error preparing statement: " . $db->getConnection()->error;
            $stmt->close();
            return;
        }
        echo "Подаваме име: $this->name, автор: $this->author";
        $stmt->bind_param("ss", $this->name, $this->author);

        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
            $stmt->close();
            return;
        }
        
        $stmt->bind_result($id);
        if ($stmt->fetch()) {
            $this->id = $id;
            echo "Идентификаторът е $id";
        } else {
            echo "No matching record found.";
        }
        $stmt->close();
    }

    public function addCollaborator($db, $collaboratorFN) {
        // role 0 means regular, 1 is PM
        // check if the user isn't already a collaborator!
        $this->loadCollaborators($db);
        $fM_collabs = implode(",", $this->collaborators);
        if (array_search($collaboratorFN, $this->collaborators, true) !== false) {
            echo "Такъв потребител вече е добавен!";
            return false;
        } // the collaborator has already been added
            

        $query = "INSERT INTO participants_in_projects (user_facultyNum, project_id, role) VALUES (?, ?, 0)";
        $stmt = $db->getConnection()->prepare($query);
        if ($this->id === null) {
            $this->retrieveID($db);
        }
        $stmt->bind_param("si", $collaboratorFN, $this->id);

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

    public function removeCollaborator($db, $collaboratorFN) {
        // role 0 means regular, 1 is PM
        $query = "DELETE FROM participants_in_projects WHERE user_facultyNum = ? AND project_id = ?";
        $stmt = $db->getConnection()->prepare($query);
        if ($this->id === null) {
            $this->retrieveID();
        }
        $stmt->bind_param("si", $collaboratorFN, $this->id);

        $stmt->execute();
        // check how many users are left on this project
        if ($stmt->affected_rows > 0) {
            $query = "SELECT * FROM participants_in_projects WHERE project_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->store_result();
            $this->loadCollaborators($db);

            // check if it was the last user, who was collaborating on the project
            if ($stmt->num_rows === 0) {
                $this->deleteProjectFromDB($db);
            } 
        } else {
            echo "Настъпи проблем при изтриването на кортеж от таблицата много към много на проектите и потребителите към тях!";
        }
        $stmt->close();
    }

    public function linkRequirementToProject($db, $requirement) {
        
        if ($this->id === null) {
            $this->retrieveID();
        }
        $requirementID = $requirement->getID();
        
        $query = "SELECT * FROM requirements_in_projects WHERE requirement_id = ? AND project_id = ?";
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bind_param("ii", $requirementID, $this->id);
        $stmt->execute();
        $stmt->store_result();

        // check if it was already linked - if it was not, link it
        if ($stmt->num_rows === 0) {
            $stmt->free_result();

            $query = "INSERT INTO requirements_in_projects (requirement_id, project_id, priority) VALUES (?, ?, ?)";
            $stmt = $db->getConnection()->prepare($query);
            $priority = $requirement->getPriority();
            $stmt->bind_param("iii", $requirementID, $this->id, $priority);

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

    public function unlinkRequirementFromProject($db, $requirement) {
        $query = "DELETE FROM requirements_in_projects WHERE requirement_id = ? AND project_id = ?";
        $stmt = $db->getConnection()->prepare($query);
        if ($this->id === null) {
            $this->retrieveID();
        }

        $requirementID = $requirement->getID();
        $stmt->bind_param("ii", $requirementID, $this->id);

        $stmt->execute();
        // check how many projects this requirement is still used in
        if ($stmt->affected_rows > 0) {
            $query = "SELECT * FROM requirements_in_projects WHERE requirement_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $requirementID);
            $stmt->execute();
            $stmt->store_result();

            // check if it was the last user, who was collaborating on the project
            if ($stmt->num_rows === 0) {
                $requirement->removeRequirementFromDB($db);
            } 
        }
        else {
            echo "Настъпи проблем при изтриването на кортеж от таблицата много към много на проектите и изискванията към тях!";
        }
        $stmt->close();
    }

    //  TODO add a user story to project / remove a user story (analogically to the one above)


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

    public function deleteProjectFromDB($db) {
        $query = "DELETE FROM projects WHERE id = ?";
        $stmt = $db->getConnection()->prepare($query);
        if ($this->id === null) {
            $this->retrieveID();
        }
        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        // check if the deletion query went as planned
        if ($stmt->affected_rows < 0) {
            echo "Настъпи проблем при изтриването на кортеж от същинското множество същности проекти!";
        }        
    }

    public function getName() {
        return $this->name;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function equals($other) {
        if (!$other instanceof Project)
            return false;
        return $other.getName() == $this->name && $other.getAuthor() == $this->author;
    }

    private function loadCollaborators($db) {
        if ($this->id === null) {
            $this->retrieveID($db);
        }

        $this->collaborators = [];
        $query = "SELECT user_facultyNum FROM participants_in_projects WHERE project_id = ?";
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $stmt->bind_result($collab);
        while ($stmt->fetch()) {
            $this->collaborators[] = $collab;
        }
    }
}
?>
