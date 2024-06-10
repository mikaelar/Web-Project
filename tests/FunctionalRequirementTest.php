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
        $emptyPriorityCollection = self::generatePriorityCollection();
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
        $requirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, null, self::DEFAULT_CRUCIAL_PRIORITY);
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
        $requirement = new FunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_CRUCIAL_PRIORITY, self::DEFAULT_CRUCIAL_PRIORITY);
        $this->assertEquals(self::EMPTY_ARR, $requirement->getComments());
        $requirement->appendComment($comment);
        $arr = [];
        $arr[] = $comment;
        $this->assertEquals($arr, $requirement->getComments());
        // check if object is passed by ref
        $this->assertTrue($arr[0]->equals($requirement->getComments()[0]));
        array_splice($requirement->getComments(), 0, 1);
        $this->assertEquals(self::EMPTY_ARR, $requirement->getComments());
    }

    // #[Test]
    // public function testSetID()
    // {

    // }

    // #[Test]
    // public function testSetID()
    // {

    // }

    // #[Test]
    // public function testSetID()
    // {

    // }

    // #[Test]
    // public function testSetID()
    // {

    // }


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
}
?>