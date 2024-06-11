<?php
namespace Tests;

use App\Requirement\adtRequirement;
use App\Requirement\NonFunctionalRequirement;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NonFunctionalRequirementTest extends TestCase
{
    private const DEFAULT_ID = 0;
    private const DEFAULT_HEADING = "Registration speed";
    private const DEFAULT_DESCRIPTION = "The most basic func, which no one takes seriously, but you can't live without it :)";
    private const DEFAULT_CRUCIAL_PRIORITY = "CruciAl";
    private const DEFAULT_ACCEPTANCE_CRITERIA = "It is what it is.";
    private const EFFICIENCY = "EffICIENCY";
    private const METRIC_VALUE = "HIGH";

    private const EMPTY_ARR = [];
    private const ALLOWED_PRIORITY_VALUES = ["crucial", "high", "medium", "low"];

    #[Test]
    public function succesfulNonFuncRequirementCreation()
    {
        $requirement = new NonFunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY,
        self::DEFAULT_ACCEPTANCE_CRITERIA, self::EFFICIENCY, self::METRIC_VALUE);
        // all others have been covered by adt requirement
        $this->assertEquals(self::DEFAULT_ACCEPTANCE_CRITERIA, $requirement->getAcceptanceCriteria());
        $metric = ["efficiency" => self::METRIC_VALUE];
        $this->assertEquals($metric, $requirement->getMetric());
    }

    #[Test]
    public function testSetAcceptanceCriteria()
    {
        $requirement = new NonFunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY,
        adtRequirement::EMPTY_STRING, self::EFFICIENCY, self::METRIC_VALUE);
        // all others have been covered by adt requirement
        $this->assertEquals(null, $requirement->getAcceptanceCriteria());
        $requirement->setAcceptanceCriteria(self::DEFAULT_ACCEPTANCE_CRITERIA);
        $this->assertEquals(self::DEFAULT_ACCEPTANCE_CRITERIA, $requirement->getAcceptanceCriteria());
        $requirement->setAcceptanceCriteria(null);
        $this->assertEquals(null, $requirement->getAcceptanceCriteria());
    }

    #[Test]
    public function testMetric()
    {
        $this->expectException(\InvalidArgumentException::class); // if null for metric is set
        $requirement = new NonFunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY,
        adtRequirement::EMPTY_STRING, null, self::METRIC_VALUE);

        $this->expectException(\InvalidArgumentException::class); // if a bulgarin word is used
        $requirement = new NonFunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY,
        adtRequirement::EMPTY_STRING, "производителност", self::METRIC_VALUE);

        $requirement = new NonFunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY,
        adtRequirement::EMPTY_STRING, self::EFFICIENCY, self::METRIC_VALUE);
        $metric = [self::EFFICIENCY => self::METRIC_VALUE];
        $this->assertEquals($metric, $requirement->getMetric());

        $NEW_VALUE = "low";
        $requirement->updateMetricValue($NEW_VALUE);
        $metric = [self::EFFICIENCY => $NEW_VALUE];
        $this->assertEquals($metric, $requirement->getMetric());
    }

    #[Test]
    public function testAddSubrequirement()
    {
        $requirement = new NonFunctionalRequirement(self::DEFAULT_ID, self::DEFAULT_HEADING, self::DEFAULT_DESCRIPTION, self::DEFAULT_CRUCIAL_PRIORITY,
        adtRequirement::EMPTY_STRING, self::EFFICIENCY, self::METRIC_VALUE);
        $this->assertEquals($requirement->getSubrequirements(), []);
        $requirement->addSubrequirement(self::DEFAULT_ID + 1, "new sub", "random stuff", null, "tommorow needs to be done", "usability", "everything is achievable within 4 clicks!");
        $subrequirement = $requirement->getSubrequirements()[0];
        $this->assertInstanceOf(NonFunctionalRequirement::class, $subrequirement);
        $objectifiedSubrequirement = new NonFunctionalRequirement(self::DEFAULT_ID + 1, "new sub", "random stuff", null, "tommorow needs to be done", "usability", "everything is achievable within 4 clicks!");
        $this->assertTrue($subrequirement->equals($objectifiedSubrequirement));
    }
}

?>