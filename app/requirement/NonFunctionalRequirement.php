<?php
namespace App\Requirement;
use App\Requirement\adtRequirement;

class NonFunctionalRequirement extends adtRequirement
{

    public function __construct($id, $heading, $description, $priority, $acceptanceCriteria, $metricName, $metricValue)
    {
        parent::__construct($id, $heading, $description, $priority);
        $this->setAcceptanceCriteria($acceptanceCriteria);
        $this->setMetric($metricName, $metricValue); // pair of key/value
    }
    // https://en.wikipedia.org/wiki/Non-functional_requirement
    public const ALLOWED_METRICS = [
        "efficiency", "usability", "reliability", "dependability", "testability", "security",
        "accessability", "portability", "fault tolerance", "integrity", "elasticy", "effectiveness",
        "open source", "operability", "interoperability", "maintainability", "internationalization", 
        "reusability", "scalability", "supportability", "extensability", "throughput", "integrability", 
        "stability", "response time", "quality", "platform", "transperancy"
    ];

    public function getAcceptanceCriteria()
    {
        return $this->acceptanceCriteria;
    }

    public function setAcceptanceCriteria($acceptanceCriteria)
    {
        if ($acceptanceCriteria === adtRequirement::EMPTY_STRING)
            $acceptanceCriteria = null;
        $this->acceptanceCriteria = $acceptanceCriteria;
    }

    public function getMetric()
    {
        return $this->metric;
    }

    private function setMetric($metricName, $metricValue)
    {
        if ($metricName === null)
            $metricName = adtRequirement::EMPTY_STRING;

        $metricName = mb_strtolower($metricName, "UTF-8");
        if (!in_array($metricName, self::ALLOWED_METRICS, true))
        {
            $ALLOWED_VALUES_STRING = implode(", ", self::ALLOWED_METRICS);
            throw new \InvalidArgumentException("The provided metric {$metricName} is not a valid metric in the currently supported ones. They are {$ALLOWED_VALUES_STRING}");
        }
        
        $this->metric = [$metricName => $metricValue];
    }

    public function updateMetricValue($metricValue)
    {
        $metricName = array_key_first($this->metric);
        $this->metric[$metricName] = $metricValue;
    }

    public function addSubrequirement($id, $heading, $description, $priority, $acceptanceCriteria, $metricName, $metricValue)
    {
        $requirement = new NonFunctionalRequirement($id, $heading, $description, $priority, $acceptanceCriteria, $metricName, $metricValue);
        $this->appendSubrequirement($requirement);
    }

    private $acceptanceCriteria;
    private $metric;
}
?>