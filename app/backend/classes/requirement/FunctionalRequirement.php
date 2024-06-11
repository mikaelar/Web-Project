<?php
namespace App\Backend\Classes\Requirement;

use App\Backend\Classes\Requirement\adtRequirement;

class FunctionalRequirement extends adtRequirement
{
    public function __construct($id, $heading, $description, $priority)
    {
        parent::__construct($id, $heading, $description, $priority);
    }

    public function addSubrequirement($id, $heading, $description, $priority)
    {
        $requirement = new FunctionalRequirement($id, $heading, $description, $priority);
        $this->appendSubrequirement($requirement);
    }
}
?>