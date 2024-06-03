<?php
// namespace App\Requirement;

// use App\Requirement\iRequirement;

require __DIR__ . '/../Comment.php';
require __DIR__ . '/iRequirement.php';

abstract class adtRequirement implements iRequirement
{
    public function __construct($id, $heading, $description, $priority)
    {
        $this->setID($id);
        $this->setHeading($heading);
        $this->setDescription($description);
        $this->setPriority($priority);
        $this->$comments = [];
        $this->$depensOnRequirements = [];
        $this->$impactsRequirements = [];
        $this->$subrequirements = [];
    }

    public function getID()
    {
        return $this->id;
    }
    
    public function getHeading()
    {
        return $this->heading;
    }
    
    public function getDescription()
    {
        return $this->description;
    }

    public function getPrioirity()
    {
        return $this->priority;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function getDependantRequirements()
    {
        return $this->depensOnRequirements;
    }

    public function getImpactedRequirements()
    {
        return $this->impactsRequirements;
    }

    public function getSubrequirements()
    {
        return $this->subrequirements;
    }


    private static $FIELD_INVALID_MESSAGE = 'Field %s of adtRequirement class cannot be %s!';
    private static $EMPTY_STRING = "";
    private static $INVALID_PRIORITY_MESSAGE = 'Value %s provided to field %s of adtRequirement class is invalid! Valid values are {%s} or null';
    public static $ALLOWED_PRIORITY_VALUES = ["crucial", "high", "medium", "low"];


    // TODO THINK about how project is loaded - do we need all these setters, do we need writing funcs in child classes or can the parent orchestrate the writing to DB funcs

    private function setID($id)
    {
        if ($id === null)
            throw new Exception(sprintf(Comment::$FIELD_INVALID_MESSAGE, "id", "null"), 1);
        $this->id = $id;
    }

    public function setHeading($heading)
    {
        $this->heading = $heading; // each heading is valid, as a heading may not be defined as of current time
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setPrioirity($priority)
    {
        if ($priority !== null && in_array($priority, adtRequirement::$ALLOWED_PRIORITY_VALUES, true)) 
        {
            $validValues = implode(", ", adtRequirement::$ALLOWED_PRIORITY_VALUES);
            throw new Exception(sprintf(Comment::$INVALID_PRIORITY_MESSAGE, $priority, "priority", $validValues), 1);
        }
        $this->priority = $priority;
    }

    // private function loadComments()
    // {
       
    // }
    // private function loadDependantRequirements()
    // {
        
    // }
    // private function loadImpactedRequirements()
    // {
    // }
    // private function loadSubrequirements()
    // {
    // }

    // TODO implement basic private functions to cover the collections

    public function appendComment(&$comment)
    {
        $this->comments->array_push($comment);
    }

    public function addComment($id, $author, $date, $content)
    {
        $comment = new Comment($id, $author, $date, $content);
        $this->appendComment($comment);
    }

    public function removeComment($comment)
    {
        $removedIndex = array_search($comment, $this->comments, true);
        if ($removedIndex !== false)
        {
            unset($this->comments[$removedIndex]);
            // NB! unset doesn't recalibrate the indexes
            $this->comments = array_values($this->comments);
        }
    }

    public function clearComments()
    {
        $this->comments = [];
    }

    public function markRequirementToDependOn(&$requirement, $impactCoefficient)
    {
        $nestedRequirement = new stdClass($requirement, $impactCoefficient);
        $this->depensOnRequirements->array_push($nestedRequirement);
    }

    // from here think about requirements with priorities

    public function changeImpactCoefficientOnDependantRequirement(&$requirement, $author, $date, $content)
    {
        $requirement = new Comment($id, $author, $date, $content);
        $this->appendComment($requirement);
    }

    public function removeComment($requirement)
    {
        $removedIndex = array_search($requirement, $this->depensOnRequirements, true);
        if ($removedIndex !== false)
        {
            unset($this->depensOnRequirements[$removedIndex]);
            // NB! unset doesn't recalibrate the indexes
            $this->depensOnRequirements = array_values($this->depensOnRequirements);
        }
    }

    public function clearComments()
    {
        $this->depensOnRequirements = [];
    }

    protected $id; // $this->impactsRequirements = null;
    protected $heading;
    protected $description;
    protected $priority; // this should be a string crucial, high, medium, low
    protected $comments;
    //protected $basedOnUserStories;
    protected $depensOnRequirements;
    protected $impactsRequirements;
    protected $subrequirements;
    // private $versions;
    
}

?>