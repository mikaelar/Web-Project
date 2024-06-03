<?php
    require __DIR__ . '/adtRequirement.php';

    class FunctionalRequirement extends adtRequirement
    {
        public function __construct($id, $heading, $description, $priority)
        {
            parent::__construct($id, $heading, $description, $priority);
        }
    }
?>