<?php
namespace App\Backend\Classes\Requirement;

use App\Backend\Classes\Requirement\adtRequirement;

class NonFunctionalRequirement extends adtRequirement
{

    public function __construct($heading, $description, $priority, $acceptanceCriteria, $metricName, $metricValue, $author)
    {
        parent::__construct($heading, $description, $priority);
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

    public function addSubrequirement($heading, $description, $priority, $acceptanceCriteria, $metricName, $metricValue, $author)
    {
        $requirement = new NonFunctionalRequirement($heading, $description, $priority, $acceptanceCriteria, $metricName, $metricValue, $author);
        $this->appendSubrequirement($requirement);
    }

    public function addRequirementToDB($db) {
        // user story id and parent requirement?
        $query = "SELECT * FROM requirements WHERE heading = ? AND author = ?";
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bind_param("ss", $this->heading, $this->author);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows === 0) {
            $query = "INSERT INTO requirements (heading, description, type, author, metric_name, metric_value, acceptance_criteria) VALUES (?, ?, 1, ?, ?, ?, ?)";
            $stmt = $db->getConnection()->prepare($query);
            $metricName = array_key_first($this->metric);
            $metricValue = array_values($this->metric)[0];
            $stmt->bind_param("ssssss", $this->heading, $this->description, $this->author, $metricName, $metricValue, $this->acceptanceCriteria);

            if ($stmt->execute()) {
                $stmt->close();
                $this->id = $stmt->insert_id;
                return true;
            } else {
                echo "Error: " . $stmt->error;
                $stmt->close();
                return false;
            }
        }
    }

    protected function retrieveID($db) {
        parent::retrieveIDAbstractly($db, 1);
    }

    private $acceptanceCriteria;
    private $metric;
}
?>