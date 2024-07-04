<?php
namespace App\Backend\Classes\Requirement;

use App\Backend\Classes\Requirement\iRequirement;
use App\Backend\Classes\Comment;
use App\Backend\Classes\Priority;
use InvalidArgumentException;

abstract class adtRequirement implements iRequirement
{
    public function __construct($heading, $description, $priority, $author)
    {
        $this->id = null;
        $this->setHeading($heading);
        $this->setDescription($description);
        $this->setPriority($priority);
        $this->author = $author;
        // TODO - add a lazy initialization for the collections part - depending on how DB communication works
        $this->comments = []; // array of comments
        $this->dependsOnRequirements = self::generatePriorityCollection(); // array of priorities, which have arrays of requirements
        $this->impactsRequirements = self::generatePriorityCollection(); // array of priorities, which have arrays of requirements
        $this->subrequirements = [];
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

    public function getPriority()
    {
        return self::convertPriorityObjectToValue($this->priority);
    }

    // maybe forbid these fields from getting extracted (now they are returned as copies, note that (but the objects inside are refs))
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
    public const EMPTY_STRING = "";
    private const INVALID_PRIORITY_MESSAGE = 'Value %s provided to field %s of adtRequirement class is invalid! Valid values are {%s} or null';
    public const ALLOWED_PRIORITY_VALUES = ["crucial", "high", "medium", "low"];
    public const ENCODING = 'UTF-8';


    // TODO THINK about how project is loaded - do we need all these setters, do we need writing funcs in child classes or can the parent orchestrate the writing to DB funcs

    private function setID($id)
    {
        if ($id === null)
            throw new \InvalidArgumentException(sprintf(self::FIELD_INVALID_MESSAGE, "id", "null"), 1);
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
        if ($description === self::EMPTY_STRING)
            $description = null;
        $this->description = $description;
    }

    // set the priority to an enum value or null, depending on the execution
    public function setPriority($priority)
    {
        // if the priority is not empty and it is different from the valid available ones, then throw
        $priority = self::convertPriorityStringToObject($priority);
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

    public function removeComment(& $comment)
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


    public function changeImpactCoefficientOnDependantRequirement(&$requirement, $impactCoefficient)
    {
        $this->unmarkRequirementToDependOn($requirement, true);
        $this->markRequirementToDependOn($requirement, $impactCoefficient, true);
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


    public function changeImpactCoefficientOnDependantOnThisRequirement(&$requirement, $impactCoefficient)
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

    public function appendSubrequirement(& $requirement)
    {
        // check if the appending is vialbe to achieve, so this is 100 % of subrequirement (no cycle is possible)
        // DFS $requirements subrequirements to check if current is among them
        $isOtherAlreadyAnAscendantToCurrent = self::isRequirementAnDescendantOfAnother($requirement, $this);
        $isOtherAlreadyAppendedAsSubrequirement = self::isRequirementAnDescendantOfAnother($this, $requirement);
        if (!$isOtherAlreadyAnAscendantToCurrent && ! $isOtherAlreadyAppendedAsSubrequirement)
        {
            $this->subrequirements[] = $requirement;
            return true;
        }
        return false;
    }

    public function removeSubrequirement(& $requirement)
    {
        if ($this->equals($requirement)) // you cannot remove a subrequirement by removing yourself
            return false;

        $isOtherAnDescendant = self::isRequirementAnDescendantOfAnother($this, $requirement);
        if (!$isOtherAnDescendant)
            return false;
        // it removes descendandts recursively
        $this->removeSubrequirementRecursively($requirement);
        return true;
    }

    public function clearSubrequirements()
    {
        $this->subrequirements = []; // clear only first row subrequirements
    }
    
    public function equals($other) // can be remade
    {
        // check also the obj type
        return $this->id === $other->getID();
    }

    public abstract function addRequirementToDB($db);

    protected function retrieveIDAbstractly($db, $type) {
        $query = "SELECT id FROM projects WHERE heading = ? AND author = ? AND type = ?";
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bind_param("ssi", $this->heading, $this->author, $type);

        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
        }
        
        $stmt->bind_result($id);
        $stmt->fetch();
        $this->id = $id;
        $stmt->close();
    }

    public function removeRequirementFromDB($db) {
        $query = "DELETE FROM requirements WHERE id = ?";
        $stmt = $db->getConnection()->prepare($query);
        if ($this->id === null) {
            $this->retrieveID();
        }
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            echo "Настъпи проблем при изтриването на кортеж от таблицата изисквания, след като никой проект вече не реферира към него!";
        }
        $stmt->close();
    }

    protected $id;
    protected $heading;
    protected $description;
    protected $priority; // this should be a string crucial, high, medium, low
    protected $comments;
    protected $author;
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
            // TODO ADD bg text cases? 
            case "crucial":
                return Priority::Crucial;
            case "high":
                return Priority::High;
            case "medium":
                return Priority::Medium;
            case "low":
                return Priority::Low;
            default:
                $validValues = implode(", ", self::ALLOWED_PRIORITY_VALUES);
                throw new \InvalidArgumentException(sprintf(self::INVALID_PRIORITY_MESSAGE, $priority, "priority", $validValues), 1);
        }
    }

    private static function convertPriorityObjectToValue($priorityObj)
    {
        if ($priorityObj === adtRequirement::EMPTY_STRING || !is_object($priorityObj))
            $priorityObj = null;

        switch ($priorityObj)
        {
            case Priority::Crucial;
                return 1;
            case Priority::High;
                return 2;
            case Priority::Medium:
                return 3;
            case Priority::Low:
                return 4;
            default:
                return 5;
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
        for ($i = 0; $i < $elementsCount; $i++)
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
        for ($i = 0; $i < $subcategoiesCount; $i++)
        {
            $isFound = self::removeElementFromArray($collection[$i], $elementToRemove);
            if ($isFound)
                return true;
        }
        return false;
    }

    private static function isRequirementAnDescendantOfAnother($possibleParent, $possibleChild)
    {
        if ($possibleParent->equals($possibleChild)) // if the current parent is the searched Child
            return true;
        $children = $possibleParent->getSubrequirements(); // if the current element has no more children, stop traversing
        if (count($children) === 0)
            return false;

        // if children are available, check them recursively by DFSing // check if cycle will exists - no cycles can be created
        foreach ($children as $child)
        {
            $isSubChild = self::isRequirementAnDescendantOfAnother($child, $possibleChild);
            if ($isSubChild)
                return true;
        }
    }

    // it is guaranteed that the requirement is present
    private function removeSubrequirementRecursively(& $searchedChild)
    {
        // NOTE - a requirement is not a subrequirement of itself!

        $children = $this->getSubrequirements(); // if the current element has no more children, stop traversing
        if (count($children) === 0)
            return false;

        // it will tell us if a subrequirement has been matched and we can also update the children tree
        $isFound = self::removeElementFromArray($children, $searchedChild);
        if ($isFound) {
            $this->setSubRequirements($children);
            return true;
        }

        // if children are available, check them recursively by DFSing // check if cycle will exists - no cycles can be created
        foreach ($children as $child)
        {
            $isFound = $child->removeSubrequirementRecursively($searchedChild);
            if ($isFound)
                break;
        }

        return $isFound;
    }

    // will be used by removeSubrequirements to keep encapsulation and update the requirement object's subrequirements array
    private function setSubRequirements($subrequirements)
    {
        $this->subrequirements = $subrequirements;
    }
}

?>