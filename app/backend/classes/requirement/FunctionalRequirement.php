<?php
namespace App\Backend\Classes\Requirement;

use App\Backend\Classes\Requirement\adtRequirement;

class FunctionalRequirement extends adtRequirement
{
    public function __construct($heading, $description, $priority, $author)
    {
        parent::__construct($heading, $description, $priority, $author);
    }

    public function addSubrequirement($heading, $description, $priority, $author)
    {
        $requirement = new FunctionalRequirement($heading, $description, $priority, $author);
        $this->appendSubrequirement($requirement);
    }

    public function addRequirementToDB($db) {
        // user story id and parent requirement?
        $query = "INSERT INTO requirements (heading, description, type, author) VALUES (?, ?, 0, ?)";
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bind_param("sis", $this->heading, $this->description, $this->author);

        if ($stmt->execute()) {
            $this->id = $stmt->insert_id;
            $stmt->close();
            return true;
        } else {
            echo "Error: " . $stmt->error;
            $stmt->close();
            return false;
        }
    }

    protected function retrieveID($db) {
        parent::retrieveIDAbstractly($db, 0);
    }
}
?>