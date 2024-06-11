<?php
namespace Tests;

use App\Requirement\adtRequirement;
use App\Requirement\FunctionalRequirement;
use App\Comment;
use App\Priority;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FunctionalRequirementTest extends TestCase
{
    private const DEFAULT_ID = 0;
    private const DEFAULT_HEADING = "Registration";
    private const DEFAULT_DESCRIPTION = "The most basic func, which no one takes seriously, but you can't live without it :)";
    private const DEFAULT_CRUCIAL_PRIORITY = "CruciAl";
    private const EMPTY_ARR = [];
    private const ALLOWED_PRIORITY_VALUES = ["crucial", "high", "medium", "low"];

    #[Test]
    public function succesfulFuncRequirementCreation()
    {
        $requirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $this->assertEquals(self::DEFAULT_ID, $requirement->getID());
        $this->assertEquals(self::DEFAULT_HEADING, $requirement->getHeading());
        $this->assertEquals(self::DEFAULT_DESCRIPTION, $requirement->getDescription());
        $this->assertEquals(self::convertPriorityStringToObject(self::DEFAULT_CRUCIAL_PRIORITY), $requirement->getPrioirity());
        $this->assertEquals(self::EMPTY_ARR, $requirement->getComments());
        $emptyPriorityCollection = array([],[],[],[],[]);
        $this->assertEquals($emptyPriorityCollection, $requirement->getDependantRequirements());
        $this->assertEquals($emptyPriorityCollection, $requirement->getDependantRequirements());
        $this->assertEquals(self::EMPTY_ARR, $requirement->getSubrequirements());
    }

    // test the setters for setting and throws

    #[Test]
    public function testSetID()
    {
        // it is a private func, half of it is covered in the successfulFuncRequirementCreation
        $this->expectException(\InvalidArgumentException::class);
        $requirement = new FunctionalRequirement(null, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $message = 'Field id of adtRequirement class cannot be null!';
        $this->expectExceptionMessage($message);
    }

    #[Test]
    public function testSetHeading()
    {
        $requirement = new FunctionalRequirement(self::DEFAULT_ID, null, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $this->assertEquals(null, $requirement->getHeading());
        $requirement->setHeading(self::DEFAULT_HEADING);
        $this->assertEquals(self::DEFAULT_HEADING, $requirement->getHeading());
        $requirement->setHeading(adtRequirement::EMPTY_STRING);
        $this->assertEquals(null, $requirement->getHeading());
    }

    #[Test]
    public function testDescription()
    {
        $requirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, null, self::DEFAULT_CRUCIAL_PRIORITY);
        $this->assertEquals(null, $requirement->getDescription());
        $requirement->setDescription(self::DEFAULT_DESCRIPTION);
        $this->assertEquals(self::DEFAULT_DESCRIPTION, $requirement->getDescription());
        $requirement->setDescription(adtRequirement::EMPTY_STRING);
        $this->assertEquals(null, $requirement->getDescription());
    }

    #[Test]
    public function testPriorityObjectConversion()
    {
        // all of these are convertable to null
        $this->assertEquals(null, self::convertPriorityStringToObject(adtRequirement::EMPTY_STRING));
        $this->assertEquals(null, self::convertPriorityStringToObject(null));
        $this->assertEquals(null, self::convertPriorityStringToObject([]));
        $this->assertEquals(null, self::convertPriorityStringToObject(1));
        
        // no matter the capital letters, everything gets converted to lower
        $this->assertEquals(Priority::Crucial, self::convertPriorityStringToObject(self::DEFAULT_CRUCIAL_PRIORITY));
        $this->assertEquals(Priority::High, self::convertPriorityStringToObject("HIGH"));
        $this->assertEquals(Priority::Medium, self::convertPriorityStringToObject("medium"));
        $this->assertEquals(Priority::Low, self::convertPriorityStringToObject("LoW"));

        // if it is a non-supported string, it will throw an error (possible BG variants in the future are listed below as invalid currently)
        $this->expectException(\InvalidArgumentException::class);
        self::convertPriorityStringToObject("критичен");
        $message = 'Value критичен provided to field priority of adtRequirement class is invalid! Valid values are crucial, high, medium, low or null';
        $this->expectExceptionMessage($message);
        
        $this->expectException(\InvalidArgumentException::class);
        self::convertPriorityStringToObject("висок");
        $message = 'Value висок provided to field priority of adtRequirement class is invalid! Valid values are crucial, high, medium, low or null';
        $this->expectExceptionMessage($message);

        $this->expectException(\InvalidArgumentException::class);
        self::convertPriorityStringToObject("среден");
        $message = 'Value среден provided to field priority of adtRequirement class is invalid! Valid values are crucial, high, medium, low or null';
        $this->expectExceptionMessage($message);

        $this->expectException(\InvalidArgumentException::class);
        self::convertPriorityStringToObject("нисък");
        $message = 'Value нисък provided to field priority of adtRequirement class is invalid! Valid values are crucial, high, medium, low or null';
        $this->expectExceptionMessage($message);
    }

    #[Test]
    public function testSetPriority()
    {
        $requirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $this->assertEquals(Priority::Crucial, $requirement->getPrioirity());
        $requirement->setPriority(null);
        $this->assertEquals(null, $requirement->getPrioirity());
        $this->expectException(\InvalidArgumentException::class);
        $requirement->setPriority("висок");
        $this->assertEquals(null, $requirement->getPrioirity());
    }

    #[Test]
    public function testAppendComment()
    {
        $comment = new Comment(self::DEFAULT_ID, "Antoan", date("d-m-Y", 1717757422), "Random stuff");
        $requirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $this->assertEquals(self::EMPTY_ARR, $requirement->getComments());

        $requirement->appendComment($comment);
        $arr = array($comment);
        $this->assertEquals($arr, $requirement->getComments());
        // check if object is passed by ref -> it is, although the arrays are different
        $this->assertTrue($arr[0]->equals($requirement->getComments()[0]));
        $comments = $requirement->getComments();
        array_splice($comments, 0, 1);
        // note that getComments returns a copy, which guarantees us encapsulation!
        $this->assertEquals($arr, $requirement->getComments());
    }

    #[Test]
    public function testAddComment()
    {
        $comment = new Comment(self::DEFAULT_ID, "Antoan", date("d-m-Y", 1717757422), "Random stuff");
        $arr = array($comment);

        $requirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $requirement->addComment(self::DEFAULT_ID, "Antoan", date("d-m-Y", 1717757422), "Random stuff");
        $this->assertTrue($arr[0]->equals($requirement->getComments()[0]));
    }

    #[Test]
    public function testRemoveComment()
    {
        $comment = new Comment(self::DEFAULT_ID, "Antoan", date("d-m-Y", 1717757422), "Random stuff");
        $requirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $requirement->addComment(self::DEFAULT_ID, "Antoan", date("d-m-Y", 1717757422), "Random stuff");

        $requirement->removeComment($comment);
        $this->assertEquals([], $requirement->getComments());
    }

    #[Test]
    public function testClearComments()
    {
        $comment = new Comment(self::DEFAULT_ID, "Antoan", date("d-m-Y", 1717757422), "Random stuff");
        $requirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $requirement->appendComment($comment);
        $commentTwo = new Comment(self::DEFAULT_ID + 1, "Mikaela", date("d-m-Y", 1717752422), "More Random stuff");
        $requirement->appendComment($commentTwo);
        // will change of object comment one reflect on the array? It should (no way to test as mutators are private!)
        $arr = array($comment, $commentTwo);
        $this->assertEquals($arr, $requirement->getComments());
        $this->assertTrue($arr[0]->equals($requirement->getComments()[0]));
        $this->assertTrue($arr[1]->equals($requirement->getComments()[1]));
        $requirement->clearComments();
        $this->assertEquals([], $requirement->getComments());
    }

    public function testPriorityObjectToValue()
    {
        $priority = null;
        $PRIORITY_OBJECT_NOT_SET_VALUE = 5; // treat it as a const
        $this->assertEquals($PRIORITY_OBJECT_NOT_SET_VALUE, self::convertPriorityObjectToValue(adtRequirement::EMPTY_STRING));
        $this->assertEquals($PRIORITY_OBJECT_NOT_SET_VALUE, self::convertPriorityObjectToValue(null));
        $this->assertEquals($PRIORITY_OBJECT_NOT_SET_VALUE, self::convertPriorityObjectToValue([]));
        $this->assertEquals($PRIORITY_OBJECT_NOT_SET_VALUE, self::convertPriorityObjectToValue(2));


        $this->assertEquals(1, self::convertPriorityObjectToValue(Priority::Crucial));
        $this->assertEquals(2, self::convertPriorityObjectToValue(Priority::High));
        $this->assertEquals(3, self::convertPriorityObjectToValue(Priority::Medium));
        $this->assertEquals(4, self::convertPriorityObjectToValue(Priority::Low));
    }

    #[Test]
    public function testMarkRequirementToDependOn()
    {
        $independantRequirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $dependantRequirement = new FunctionalRequirement(self::DEFAULT_ID + 1, "Login", "Not your average Jilly Joe functionality :)", "high");

        $this->assertEquals($independantRequirement->getImpactedRequirements(), $dependantRequirement->getDependantRequirements());
        // when updating the dependancies, both list should be updated with each other

        // dependancy is high and the initiator is the dependantRequirement for this linkage
        $dependantRequirement->markRequirementToDependOn($independantRequirement, "high", true); 
        // remember that each of these collection is an array of 5 elements (priorities), each of which is an array of requirements!
        // starting from 1 which is crucial to 5 which is undefined
        // BUUUUUUUUUUUUUUUUUUT! arrays are counted from 0, so index 1
        $this->assertTrue($independantRequirement->getImpactedRequirements()[1][0]->equals($dependantRequirement));
        $this->assertTrue($dependantRequirement->getDependantRequirements()[1][0]->equals($independantRequirement));
    }

    #[Test]
    public function testUnmarkRequirementToDependOn()
    {
        $independantRequirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $dependantRequirement = new FunctionalRequirement(self::DEFAULT_ID + 1, "Login", "Not your average Jilly Joe functionality :)", "high");
        
        $dependantRequirement->markRequirementToDependOn($independantRequirement, "high", true); 
        $this->assertTrue($independantRequirement->getImpactedRequirements()[1][0]->equals($dependantRequirement));
        $this->assertTrue($dependantRequirement->getDependantRequirements()[1][0]->equals($independantRequirement));

        $dependantRequirement->unmarkRequirementToDependOn($independantRequirement, true);
        $this->assertEquals($independantRequirement->getImpactedRequirements()[1], []);
        $this->assertEquals($dependantRequirement->getDependantRequirements()[1], []);
    }

    #[Test]
    public function testChangeImpactCoefficientOnDependantRequirement()
    {
        $independantRequirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $dependantRequirement = new FunctionalRequirement(self::DEFAULT_ID + 1, "Login", "Not your average Jilly Joe functionality :)", "high");
        $dependantRequirement->markRequirementToDependOn($independantRequirement, "high", true); 
        $this->assertTrue($independantRequirement->getImpactedRequirements()[1][0]->equals($dependantRequirement));
        $this->assertTrue($dependantRequirement->getDependantRequirements()[1][0]->equals($independantRequirement));

        $dependantRequirement->changeImpactCoefficientOnDependantRequirement($independantRequirement, "low");
        $this->assertEquals($independantRequirement->getImpactedRequirements()[1], []);
        $this->assertEquals($dependantRequirement->getDependantRequirements()[1], []);
        $this->assertTrue($independantRequirement->getImpactedRequirements()[3][0]->equals($dependantRequirement));
        $this->assertTrue($dependantRequirement->getDependantRequirements()[3][0]->equals($independantRequirement));
    }

    #[Test]
    public function testClearDependanciesWhichThisRequirementLiesUpon()
    {
        $independantRequirementHigh = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $independantRequirementLow = new FunctionalRequirement(self::DEFAULT_ID + 2, "Forgotten password", "Third time is the charm", "low");
        $dependantRequirement = new FunctionalRequirement(self::DEFAULT_ID + 1, "Login", "Not your average Jilly Joe functionality :)", "high");
        
        $dependantRequirement->markRequirementToDependOn($independantRequirementHigh, "high", true);
        $dependantRequirement->markRequirementToDependOn($independantRequirementLow, "low", true);


        $dependantRequirement->clearDependanciesWhichThisRequirementLiesUpon();
        $EMPTY_PRIORITY_ARRAY = array([],[],[],[],[]);
        // TODO - the test can be extended to show that other links remain in tact
        $this->assertEquals($independantRequirementHigh->getImpactedRequirements(), $EMPTY_PRIORITY_ARRAY);
        $this->assertEquals($independantRequirementLow->getImpactedRequirements(), $EMPTY_PRIORITY_ARRAY);
        $this->assertEquals($dependantRequirement->getDependantRequirements(), $EMPTY_PRIORITY_ARRAY);
    }

    #[Test]
    public function testAddDependantRequirement()
    {
        $independantRequirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $dependantRequirement = new FunctionalRequirement(self::DEFAULT_ID + 1, "Login", "Not your average Jilly Joe functionality :)", "high");
        $this->assertEquals($independantRequirement->getImpactedRequirements(), $dependantRequirement->getDependantRequirements());
        // when updating the dependancies, both list should be updated with each other

        // dependancy is high and the initiator is the independantRequirement for this linkage
        $independantRequirement->addDependantRequirement($dependantRequirement, "high", true); 
        $this->assertTrue($independantRequirement->getImpactedRequirements()[1][0]->equals($dependantRequirement));
        $this->assertTrue($dependantRequirement->getDependantRequirements()[1][0]->equals($independantRequirement));
    }

    #[Test]
    public function testRemoveDependancyToRequirement()
    {
        $independantRequirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $dependantRequirement = new FunctionalRequirement(self::DEFAULT_ID + 1, "Login", "Not your average Jilly Joe functionality :)", "high");
        
        $independantRequirement->addDependantRequirement($dependantRequirement, "high", true); 
        $this->assertTrue($independantRequirement->getImpactedRequirements()[1][0]->equals($dependantRequirement));
        $this->assertTrue($dependantRequirement->getDependantRequirements()[1][0]->equals($independantRequirement));

        $independantRequirement->removeDependancyToRequirement($dependantRequirement, true);
        $this->assertEquals($independantRequirement->getImpactedRequirements()[1], []);
        $this->assertEquals($dependantRequirement->getDependantRequirements()[1], []);
    }

    #[Test]
    public function testChangeImpactCoefficientOnDependantOnThisRequirement()
    {
        $independantRequirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $dependantRequirement = new FunctionalRequirement(self::DEFAULT_ID + 1, "Login", "Not your average Jilly Joe functionality :)", "high");
        $independantRequirement->addDependantRequirement($dependantRequirement, "high", true); 
        $this->assertTrue($independantRequirement->getImpactedRequirements()[1][0]->equals($dependantRequirement));
        $this->assertTrue($dependantRequirement->getDependantRequirements()[1][0]->equals($independantRequirement));

        $independantRequirement->changeImpactCoefficientOnDependantOnThisRequirement($dependantRequirement, "low");
        $this->assertEquals($independantRequirement->getImpactedRequirements()[1], []);
        $this->assertEquals($dependantRequirement->getDependantRequirements()[1], []);
        $this->assertTrue($independantRequirement->getImpactedRequirements()[3][0]->equals($dependantRequirement));
        $this->assertTrue($dependantRequirement->getDependantRequirements()[3][0]->equals($independantRequirement));
    }

    #[Test]
    public function testClearDependanciesBasedOnThisRequirement()
    {
        $independantRequirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $dependantRequirementLow = new FunctionalRequirement(self::DEFAULT_ID + 2, "Forgotten password", "Third time is the charm", "low");
        $dependantRequirementHigh = new FunctionalRequirement(self::DEFAULT_ID + 1, "Login", "Not your average Jilly Joe functionality :)", "high");
        
        $independantRequirement->addDependantRequirement($dependantRequirementHigh, "high", true); 
        $independantRequirement->addDependantRequirement($dependantRequirementLow, "high", true); 

        $independantRequirement->clearDependanciesBasedOnThisRequirement();
        $EMPTY_PRIORITY_ARRAY = array([],[],[],[],[]);
        // TODO - the test can be extended to show that other links remain in tact
        $this->assertEquals($independantRequirement->getImpactedRequirements(), $EMPTY_PRIORITY_ARRAY);
        $this->assertEquals($dependantRequirementLow->getDependantRequirements(), $EMPTY_PRIORITY_ARRAY);
        $this->assertEquals($dependantRequirementHigh->getDependantRequirements(), $EMPTY_PRIORITY_ARRAY);
    }

    #[Test]
    public function testAppendSubrequirement()
    {
        $grandParentRequirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $parentRequirement = new FunctionalRequirement(self::DEFAULT_ID + 1, "Check email", "Checking for email if it is valid", null);
        $succesfulAppending = $grandParentRequirement->appendSubrequirement($parentRequirement);
        $this->assertTrue($succesfulAppending); // true as it appended it now

        $succesfulAppending = $grandParentRequirement->appendSubrequirement($parentRequirement);
        $this->assertFalse($succesfulAppending); // it was already appended
        $succesfulAppending = $parentRequirement->appendSubrequirement($grandParentRequirement);
        $this->assertFalse($succesfulAppending); // false, because parent is child of grandparent!

        $childRequirement = new FunctionalRequirement(self::DEFAULT_ID + 2, "Check the RFC 5322 standard", "The email should correspond to the RFC 5322 standard", "low");
        // checks for recursive appending
        $succesfulAppending = $parentRequirement->appendSubrequirement($childRequirement);
        $this->assertTrue($succesfulAppending); // it is appended to the tree structure

        $succesfulAppending = $grandParentRequirement->appendSubrequirement($childRequirement);
        $this->assertFalse($succesfulAppending); // following the transitive relation, child is already a descendant of grandparent
        $succesfulAppending = $childRequirement->appendSubrequirement($grandParentRequirement);
        $this->assertFalse($succesfulAppending); // child is an descendant of grandparent!

    }

    #[Test]
    public function testAddSubrequirement()
    {
        $requirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $this->assertEquals($requirement->getSubrequirements(), []);
        $requirement->addSubrequirement(self::DEFAULT_ID + 1, "new sub", "random stuff", null);
        $subrequirement = $requirement->getSubrequirements()[0];
        $this->assertInstanceOf(FunctionalRequirement::class, $subrequirement);
        $objectifiedSubrequirement = new FunctionalRequirement(self::DEFAULT_ID + 1, "new sub", "random stuff", null);
        $this->assertTrue($subrequirement->equals($objectifiedSubrequirement));
    }

    #[Test]
    public function testRemoveSubrequirement()
    {
        $grandParentRequirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $parentRequirement = new FunctionalRequirement(self::DEFAULT_ID + 1, "Check email", "Checking for email if it is valid", null);
        $childRequirement = new FunctionalRequirement(self::DEFAULT_ID + 2, "Check the RFC 5322 standard", "The email should correspond to the RFC 5322 standard", "low");
        $grandParentRequirement->appendSubrequirement($parentRequirement);
        $parentRequirement->appendSubrequirement($childRequirement);

        // remove grandparent from child -> false
        $succesfulRemoval = $childRequirement->removeSubrequirement($grandParentRequirement);
        $this->assertFalse($succesfulRemoval);

        // remove child from grandparent -> true as it is his descendant
        $succesfulRemoval = $grandParentRequirement->removeSubrequirement($childRequirement);
        $this->assertTrue($succesfulRemoval);

        // remove child from parent -> false as it was already removed by grandparent
        $succesfulRemoval = $parentRequirement->removeSubrequirement($childRequirement);
        $this->assertFalse($succesfulRemoval);

        // remove parent from grandparent -> true as it is his descendant
        $succesfulRemoval = $grandParentRequirement->removeSubrequirement($parentRequirement);
        $this->assertTrue($succesfulRemoval);

        // remove child from grandparent a second time -> false
        $succesfulRemoval = $grandParentRequirement->removeSubrequirement($childRequirement);
        $this->assertFalse($succesfulRemoval);
        
        // remove grandparent from grandparent -> false (you are not subrequirement of yourself!)
        $succesfulRemoval = $grandParentRequirement->removeSubrequirement($grandParentRequirement);
        $this->assertFalse($succesfulRemoval);
    }

    #[Test]
    public function testClearSubrequirements()
    {
        $grandParentRequirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY);
        $parentRequirement = new FunctionalRequirement(self::DEFAULT_ID + 1, "Check email", "Checking for email if it is valid", null);
        $childRequirement = new FunctionalRequirement(self::DEFAULT_ID + 2, "Check the RFC 5322 standard", "The email should correspond to the RFC 5322 standard", "low");
        $grandParentRequirement->appendSubrequirement($parentRequirement);
        $parentRequirement->appendSubrequirement($childRequirement);

        // remove grandparents subrequirements (the parent still has a referance to the grandchild)
        $grandParentRequirement->clearSubrequirements();
        $this->assertEquals($grandParentRequirement->getSubrequirements(), []);
        $this->assertTrue($parentRequirement->getSubrequirements()[0]->equals($childRequirement));
    }


    // private funcs used by adtRequirement
    private static function generatePriorityCollection()
    {
        $maxPriority = 4 + 1; // 4 values for priorities + 1 for null or non-existent
        // lower number means higher priority according to Software Requirements Analysis standards and they start from 1
        $collection = [];
        for ($i = 0; $i < $maxPriority; $i++)
            $collection[] = []; // add an empty collection to the end of the current one
        return $collection;
    }

    private static function convertPriorityStringToObject($priority)
    {
        if ($priority === adtRequirement::EMPTY_STRING || $priority === null || !is_string($priority))
            return null;

        $convertedPriority = mb_strtolower($priority, adtRequirement::ENCODING);
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
                $msg='Value %s provided to field %s of adtRequirement class is invalid! Valid values are {%s} or null';
                $validValues = implode(", ", adtRequirement::ALLOWED_PRIORITY_VALUES);
                throw new InvalidArgumentException(sprintf($msg, $priority, "priority", $validValues), 1);
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
}
?>