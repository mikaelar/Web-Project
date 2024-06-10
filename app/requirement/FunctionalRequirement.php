<?php
namespace App\Requirement;
use App\Requirement\adtRequirement;

class FunctionalRequirement extends adtRequirement
{
    public function __construct($id, $heading, $description, $priority)
    {
        parent::__construct($id, $heading, $description, $priority);
    }
}
?>