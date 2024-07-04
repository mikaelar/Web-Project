<?php
namespace App\Backend\Scripts;
require_once __DIR__ . '/../../../vendor/autoload.php';

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../../frontend/login_register/login.html");
    exit();
}

use App\Backend\Classes\Database;
use App\Backend\Classes\Project;
use App\Backend\Classes\Notifier;
 
// parse the CSV or the Tabs and execute with for the code below (it will be very slow but technical debt was already taken)

// returns an array of strings
function getOtherParticipants($db, $author, $projectID) {
    $query = "SELECT user_facultyNum FROM participants_in_projects WHERE project_id = ?";
    $stmt = $db->getConnection()->prepare($query);
    $stmt->bind_param('i', $projectID);
    $stmt->execute();
    $stmt->bind_result($userFN);
    $others = [];
    while ($stmt->fetch()) {
        if ($userFN !== $author)
            $others[] = $userFN;
    }
    $stmt->close();
    return $others;
}

// returns an array of requirements or an array of hashmaps
function getRequirementsForProject($db, $projectID) {
    // this will return which requirements to take
    // Ако искам да имам и приоритет, то трябва да събера таблици изиксвания с изисквания в проекти
    // И накрая тези резултати да филтрирам по Project id
    $query = "SELECT heading, description, type, priority, metric_name, metric_value, acceptance_criteria 
    FROM requirements_in_projects 
    INNER JOIN requirements ON requirements_in_projects.requirement_id = requirements.id
    WHERE project_id = ?";
    $stmt = $db->getConnection()->prepare($query);
    $stmt->bind_param('i', $projectID);
    $stmt->execute();
    $stmt->bind_result($heading, $description, $type, $impact, $metricName, $metricValue, $acceptance_criteria);
    $requirements = [];
    while ($stmt->fetch()) {
        $impactAsWords = "";
        switch ($impact) {
            case 1:
                $impactAsWords = "crucial";
                break;
            case 2:
                $impactAsWords = "high";
                break;
            case 3:
                $impactAsWords = "medium";
                break;
            case 4:
                $impactAsWords = "low";
                break;
        }

        $requirement = ['heading' => $heading, 'description' => $description, 'type' => $type, 'impact' => $impactAsWords];
        if ($type === 1) {
            $requirement += ['metricName' => $metricName, 'metricValue' => $metricValue, 'acceptance_criteria' => $acceptance_criteria];
        }
        $requirements[] = $requirement;
    }
    $stmt->close();
    return $requirements;
}

function serializeDateRow($row) {
    // converts the row to serialisable string
    $SEPARATOR = "\t";
    $REQUIREMENTS_SEPARATOR = "<|>";
    $REQUIREMENTS_FIELDS_SEPARATORS = "_|_";

    $otherAuthors = implode($REQUIREMENTS_FIELDS_SEPARATORS, $row['otherFNs']);
    $formattedRow = $row['author'] . $SEPARATOR . $otherAuthors . $SEPARATOR . $row['name'] . $SEPARATOR . $row['description'] . $SEPARATOR;
    // array of requirements, each of which is a hashtable
    $requirementsReadyToBeAppended = [];
    foreach ($row['requirements'] as $requirement) {
        // requirement is a hash table
        $formattedRequirement = implode($REQUIREMENTS_FIELDS_SEPARATORS, $requirement);
        $requirementsReadyToBeAppended[] = $formattedRequirement;
    }
    $serializedRequirements = implode($REQUIREMENTS_SEPARATOR, $requirementsReadyToBeAppended);
    $formattedRow .= $serializedRequirements;
    return $formattedRow;
}

$db = new Database();

$query = "SELECT id, name, description, author FROM projects";
if ($_SESSION['username'] !== "admin") {
    $query = "SELECT name, description, author FROM projects WHERE id IN (SELECT project_id FROM participants_in_projects WHERE user_facultyNum = ?)";   
}
$stmt = $db->getConnection()->prepare($query);
if ($_SESSION['username'] !== "admin") {
    $stmt->bind_param('s', $_SESSION['facultyNum']);
}
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($projectID, $name, $description, $author);

// we either have all rows (projects) for all people or have the rows, which the user participates in
if ($stmt->num_rows > 0) {
    // TODO - think about user stories in the serialization
    $data = [["Автор", "Останали факултетни номера", "Тема", "Описание и примерно разпределение", "Изисквания (отделните параметри на всяко изискване са разделени с _|_, като отделните изисквания са разделени с <|>)"]];
    
    // note that otherFNS can be empty
    while ($stmt->fetch()) {
        $otherFNs = getOtherParticipants($db, $author, $projectID);
        $requirementsForProject = getRequirementsForProject($db, $projectID);
        $data[] = ['author' => $author, 'otherFNs' => $otherFNs, 'name' => $name, 'description' => $description, 'requirements' => $requirementsForProject];
    }

    $notifier = new Notifier($db, $_SESSION['facultyNum']);
    $date = date('Y-m-d H:i:s');
    $notifier->addNotification("Успешно експортирахте информацията на БД към .tsv формат", $date);
    
    header('Content-Type: text/tab-separated-values');
    header('Content-Disposition: attachment;filename="exported_projects.tsv"');
    $output = fopen('php://output', 'w');
    

    $headerRow = implode("\t", $data[0]);
    echo "$headerRow\n";

    $dataCount = count($data);
    for ($i = 1; $i < $dataCount - 1; $i++) { 
        echo serializeDateRow($data[$i]) . "\n";
    }

    echo serializeDateRow($data[$dataCount - 1]);
    fclose($output);
} else {
    echo "<pre>";
    echo "Нямаше никакви проекти, които Вие можете да експортирате!";
    echo "</pre>";
    header("Location: ../../frontend/manage_homepage/homepage.php");
}

?>