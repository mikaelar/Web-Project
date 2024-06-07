<?php
namespace App\Requirement;

use App\Requirement\iRequirement;
use App\Comment;
use App\Priority;

abstract class adtRequirement implements iRequirement
{
    public function __construct($id, $heading, $description, $priority)
    {
        $this->setID($id);
        $this->setHeading($heading);
        $this->setDescription($description);
        $this->setPriority($priority);
        // TODO - add a lazy initialization for the collections part - depending on how DB communication works
        $this->$comments = []; // array of comments
        $this->$dependsOnRequirements = self::generatePriorityCollection(); // array of priorities, which have arrays of requirements
        $this->$impactsRequirements = self::generatePriorityCollection(); // array of priorities, which have arrays of requirements
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
        return $this->dependsOnRequirements;
    }

    public function getImpactedRequirements()
    {
        return $this->impactsRequirements;
    }

    public function getSubrequirements()
    {
        return $this->subrequirements;
    }


    private const FIELD_INVALID_MESSAGE = 'Field %s of adtRequirement class cannot be %s!';
    private const EMPTY_STRING = "";
    private const INVALID_PRIORITY_MESSAGE = 'Value %s provided to field %s of adtRequirement class is invalid! Valid values are {%s} or null';
    public const ALLOWED_PRIORITY_VALUES = ["crucial", "high", "medium", "low"];
    public const ENCODING = 'UTF-8';


    // TODO THINK about how project is loaded - do we need all these setters, do we need writing funcs in child classes or can the parent orchestrate the writing to DB funcs

    private function setID($id)
    {
        if ($id === null)
            throw new Exception(sprintf(self::$FIELD_INVALID_MESSAGE, "id", "null"), 1);
        $this->id = $id;
    }

    public function setHeading($heading)
    {
        if ($heading === self::EMPTY_STRING)
            $heading = null;
        $this->heading = $heading; // each heading is valid, as a heading may not be defined as of current time
    }

    public function setDescription($description)
    {
        if ($heading === self::EMPTY_STRING)
            $heading = null;
        $this->description = $description;
    }

    // set the priority to an enum value or null, depending on the execution
    public function setPrioirity($priority)
    {
        $priority = self::convertPriorityStringToObject($priority);
         // if the priority is not empty and it is different from the valid available ones, then throw
        if ($priority !== null && !in_array($priority, adtRequirement::$ALLOWED_PRIORITY_VALUES, true)) 
        {
            
        }

        $this->priority = $priority; // it is either enum or null
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
        $this->comments[] = $comment;
    }

    public function addComment($id, $author, $date, $content)
    {
        // date is created and is supporting the d-M-y format
        $comment = new Comment($id, $author, $date, $content);
        $this->appendComment($comment);
    }

    public function removeComment($comment)
    {
        self::removeElementFromArray($this->comments, $comment);
    }

    public function clearComments()
    {
        $this->comments = [];
    }


    // functions for requirements for which the current one depends on (they are responsible for this one) -> double update

    public function markRequirementToDependOn(&$requirement, $impactCoefficient, $isInitiator)
    {
        $position = self::convertPriorityObjectToValue(self::convertPriorityStringToObject($impactCoefficient)) - 1;
        $this->dependsOnRequirements[$position][] = $requirement; // since the collection is from 0 to n-1 and the priorities are from 0 to n
        // update other req list
        if ($isInitiator) // update the other item list
            $requirement->addDependantRequirement($this, $impactCoefficient, false);
    }


    public function changeImpactCoefficientOnDependantRequirement(&$requirement, $impactCoefficient, $isInitiator)
    {
        $this->unmarkRequirementToDependOn($requirement);
        $this->markRequirementToDependOn($requirement, $impactCoefficient);
        // other req list is automatically updated by the other two functions
    }

    public function unmarkRequirementToDependOn(&$requirement, $isInitiator)
    {
        $hasBeenRemoved = self::removeElementFrom2DArray($this->dependsOnRequirements, $requirement);

        // update other req list
        if ($isInitiator && $hasBeenRemoved)
            $requirement->removeDependancyToRequirement($this, false);
        return $hasBeenRemoved;
    }

    public function clearDependanciesWhichThisRequirementLiesUpon()
    {
        // we want to remove using unmark all dependancies
        // iterate through all elements, then for each element invoke the function to remove its dependancy
        foreach ($this->dependsOnRequirements as $priority)
        {
            foreach ($priority as $requirement)
            {
                // unset the links
                $requirement->removeDependancyToRequirement($this, false);
            }
        }
        $this->dependsOnRequirements = self::generatePriorityCollection(); // reset the current list
    }


    // functions for requirements for which the current one is responsible for (they depend on it) -> double update

    public function addDependantRequirement(&$requirement, $impactCoefficient, $isInitiator)
    {
        $position = self::convertPriorityObjectToValue(self::convertPriorityStringToObject($impactCoefficient)) - 1;
        $this->impactsRequirements[$position][] = $requirement; // since the collection is from 0 to n-1 and the priorities are from 0 to n
        // update other req list
        if ($isInitiator)
            $requirement->markRequirementToDependOn($this, $impactCoefficient, false);
    }


    public function changeImpactCoefficientOnDependantOnThisRequirement(&$requirement, $impactCoefficient, $isInitiator)
    {
        $this->removeDependancyToRequirement($requirement, true);
        $this->addDependantRequirement($requirement, $impactCoefficient, true);
        // other req list is automatically updated by the other 2 functions
    }

    public function removeDependancyToRequirement(&$requirement, $isInitiator)
    {
        $hasBeenRemoved = self::removeElementFrom2DArray($this->impactsRequirements, $requirement);
        // update other req list
        if ($isInitiator && $hasBeenRemoved)
            $requirement->unmarkRequirementToDependOn($this, false);
    }

    public function clearDependanciesBasedOnThisRequirement()
    {
        // we want to remove using unmark all dependancies
        // iterate through all elements, then for each element invoke the function to remove its dependancy
        foreach ($this->impactsRequirements as $priority)
        {
            foreach ($priority as $requirement)
            {
                // unset the links
                $requirement->unmarkRequirementToDependOn($this, false);
            }
        }
        $this->impactsRequirements = self::generatePriorityCollection(); // reset the current list
    }

    // TODO For subrequirements (like comments and unit test) - also implement a custom comparator, for which the updating to take place 
    
    
    protected $id;
    protected $heading;
    protected $description;
    protected $priority; // this should be a string crucial, high, medium, low
    protected $comments;
    //protected $basedOnUserStories;
    protected $dependsOnRequirements;
    protected $impactsRequirements;
    protected $subrequirements;
    // private $versions;

    private static function convertPriorityStringToObject($priority)
    {
        if ($priority === self::EMPTY_STRING || $priority === null || !is_string($priority))
            return null;

        $convertedPriority = mb_strtolower($priority, self::ENCODING);
        switch ($convertedPriority)
        {
            case "crucial":
                return Priority::Crucial;
            case "high":
                return Priority::High;
            case "medium":
                return Priority::Medium;
            case "low":
                return Priority::Low;
            default:
                $validValues = implode(", ", self::$ALLOWED_PRIORITY_VALUES);
                throw new Exception(sprintf(self::$INVALID_PRIORITY_MESSAGE, $priority, "priority", $validValues), 1);
        }
    }

    private static function convertPriorityObjectToValue($priorityObj)
    {
        if ($priority === self::EMPTY_STRING || $priority === null || !is_object($priority))
            return null;

        switch ($priorityObj)
        {
            case Priority::Crucial;
                return 0;
            case Priority::High;
                return 1;
            case Priority::Medium:
                return 2;
            case Priority::Low:
                return 3;
            default:
                return 4;
        }
    }

    private static function generatePriorityCollection()
    {
        $maxPriority = count(self::ALLOWED_PRIORITY_VALUES) + 1; // 4 values for priorities + 1 for null or non-existent
        // lower number means higher priority according to Software Requirements Analysis standards and they start from 1
        // alternative would be to use a hashmap with keys priorities and values arrays, I will stick to arrays on the corresponding indexes
        $collection = [];
        for ($i = 0; $i < $maxPriority; $i++)
            $collection[] = []; // add an empty collection to the end of the current one
        return $collection;
    }

    private static function removeElementFromArray(& $collection, & $elementToRemove)
    {
        $elementsCount = count($collection);
        for ($i = 0; i < $elementsCount; $i++)
        {
            if ($collection[$i]->equals($elementToRemove))
            {
                unset($collection[$i]);
                // NB! unset doesn't recalibrate the indexes
                $collection = array_values($collection);
                return true;
            }
        }
        return false;
    }

    private static function removeElementFrom2DArray(& $collection, & $elementToRemove)
    {
        $subcategoiesCount = count($collection);
        for ($i = 0; i < $subcategoiesCount; $i++)
        {
            // collection is array of arrays, collection[i] is an array
            $hasBeenRemoved = self::removeElementFromArray($collection[$i], $elementToRemove);
            if ($hasBeenRemoved)
                return true;
        }
        return false;
    }
}

?>