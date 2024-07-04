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
use App\Backend\Classes\User;
use App\Backend\Classes\Requirement\FunctionalRequirement;
use App\Backend\Classes\Requirement\NonFunctionalRequirement;

// parse the CSV or the Tabs and execute with for the code below (it will be very slow but technical debt was already taken)

function switchURL($isSuccesful) {
    if ($isSuccesful) {
        header("Location: ../../frontend/manage_homepage/homepage.php");
    } else {
        echo "Не бяха добавени нови проекти. Или не участвате в тях или вече всичките проекти са били добавени!";
        header("Location: ../../frontend/create_project/add_multiple_projects.html");
    }
}

function appendNotificationForSuccesfulImporting($db, $isSuccesful) {
    if ($isSuccesful) {
        $notifier = new Notifier($db, $_SESSION['facultyNum']);
        $date = date('Y-m-d H:i:s');
        $notifier->addNotification("Успешно импортирахте информацията от .tsv файла към БД", $date);
    }   
}

// 2 variants for file format -> 5 cols (our supported) or teachers format
function handleInput($db) {
    $tabText = $_POST['tabText'] ?? '';
    if ($tabText === null || $tabText === "") {
        echo "Не сте подали никакъв формат за преработка от скрипта, моля въведете валидни .tsv данни!";
        switchURL(false);
    }

    $tabTextArray = explode("\n", $tabText);
    if (count(str_getcsv($tabTextArray[0], "\t")) > 5) {
        handleTeachersFormatWrapper($db, $tabTextArray);
    }
    else {
        handleOurFormat($tabTextArray, $db);
    }
}

function handleTeachersFormat($tabTextArray) {
    $tabTextArraySplit = [];
    $tabTextArrayLen = count($tabTextArray); // how many rows to traverse
    for ($rowIndex = 0; $rowIndex < $tabTextArrayLen; $rowIndex++) {
        $row = $tabTextArray[$rowIndex];
        $csvArray = str_getcsv($row, "\t");
        
        // if there are any students, on this project
        $ADMINISTRATIVE_ROWS_COUNT = 3;
        if (strlen($csvArray[1]) !== 0) {

            if ($rowIndex <= $ADMINISTRATIVE_ROWS_COUNT - 1) // it is header row or example row, skip them!
                continue;
            if (!isset($csvArray[2]) || $csvArray[2] === null || $csvArray[2] === "") // it is empty, skip the line
                continue;
    
            $members = explode(";", $csvArray[2]);
            $teams = explode(";", $csvArray[1]); // split by valid fields on ; . Note that a ; can be trailling!
            $teamsCount = count($teams);

            // admin check if the user can add the current row (he should be able to only add rows he is included)
            for ($teamIndex = 0; $teamIndex < $teamsCount; $teamIndex++) {
                if ($teams[$teamIndex] !== null && strlen($teams[$teamIndex]) !== 0) {
                    // add the names -> usernames
                    $formattedMembers = trim($members[$teamIndex]);
                    $formattedMembers = preg_replace('/\s+/', " ", $formattedMembers);
                    if ($_SESSION['username'] !== "admin" && !str_contains($formattedMembers, $_SESSION['username'])) { // check if the user is contained within the project (admin can add all)
                        // the current config of users is invalid, try the other team
                        continue;
                    }
                    
                    // add the facultyNums -> keys for DB
                    $formattedTeam = trim($teams[$teamIndex]);
                    $formattedTeam = preg_replace('/\s+/', " ", $formattedTeam); // remove multiple spaces inside the name

                    $firstRequirement = $csvArray[7] ?? "";
                    $interestingCsvFields = [$formattedTeam, $formattedMembers, $csvArray[5], $csvArray[6], $firstRequirement];
                    
                    if (isset($csvArray[8]) && strlen($csvArray[8]) > 0) {
                        $interestingCsvFields[] = $csvArray[8];
                    }      
                    if (isset($csvArray[9]) && strlen($csvArray[9]) > 0) {
                        $interestingCsvFields[] = $csvArray[9];
                    }

                    $tabTextArraySplit[] = $interestingCsvFields;
                }
            }

        }
    }

    return $tabTextArraySplit;
}

function getExistingFacultyNums($db, $facultyNums) {
    // the placeholders are tricky as their length varies, that changes the query statement
    $facultyNumsCount = count($facultyNums);
    
    $placeholders = implode(',', array_fill(0, $facultyNumsCount, '?')); // generate as many placeholders as fn-s to check
    $query = "SELECT facultyNum from users WHERE facultyNum IN ($placeholders)";
    $stmt = $db->getConnection()->prepare($query);
    
    $types = str_repeat("s", $facultyNumsCount);
    $bindParams = [];
    foreach ($facultyNums as $key => $value) {
        $bindParams[$key] = &$facultyNums[$key];
    }

    // NB - bind param doesn't accept array spreading, so we need a work around
    call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $bindParams));
    $stmt->execute();
    $stmt->bind_result($fetchFN);

    $existingFacultyNums = [];
    while ($stmt->fetch()) {
        $existingFacultyNums[] = $fetchFN;
    }

    $stmt->close();
    return $existingFacultyNums;
}

function areAllMembersPresentInTheDB($members, $db) {
    $existingMembers = getExistingFacultyNums($db, $members);
    $diff = array_diff($members, $existingMembers);
    return count($diff) === 0;
}

function createMissingUsers($db, $facultyNums, $usernames, $existingFacultyNums) {
    $missingFacultyNums = [];
    $missingUsernames = [];
    foreach ($facultyNums as $key => $value) {
        // if the value is not found, then add the missing user to the list
        if (!array_search($value, $existingFacultyNums, true)) {
            $missingFacultyNums[] = $value;
            $missingUsernames[] = $usernames[$key];
        }
    }

    foreach ($missingFacultyNums as $key => $missingFN) {
        $user = new User($db);
        $DEFAULT_PASSWORD = $missingFN;
        $user->setUserDetails($missingFN, $missingUsernames[$key], $DEFAULT_PASSWORD);
        $user->registerWithoutSwappingURLs();
    }
}

function addUsers($db, $facultyNums, $usernames) {
    $existingFacultyNums = getExistingFacultyNums($db, $facultyNums);
    createMissingUsers($db, $facultyNums, $usernames, $existingFacultyNums);
}

function addRequirementToDBFromTSVRead($db, $project, $requirementNumber, $valuesArray, $author, $priority = null, $heading = null) {
    $REQUIREMENTS_START_AT = 3;
    $description = $valuesArray[$REQUIREMENTS_START_AT + $requirementNumber] ?? "";
    if ($description !== "") {
        if ($heading === null) {
            $heading = "Изисквания към участник $requirementNumber";
        }
        $requirement = new FunctionalRequirement($heading, $description, $priority, $author);
        $requirement->addRequirementToDB($db);
        $project->linkRequirementToProject($db, $requirement);

        // the code below should not throw an error, as the linking should be skipped - it doesn't hooray!
        // $thirdRequirement->addRequirementToDB($db);
        //$project->linkRequirementToProject($db, $thirdRequirement);
    }
}

function addCsvValuesToDB($csvValues, $db) {
    $isSuccesful = false;

    foreach ($csvValues as $value) {
        // FORMAT of value (arr) : facultyNums, usernames, name, description?, req1?, req2?, req3?
    
        $facultyNums = explode(", ", ($value[0] ?? ""));
        $usernames = explode(", ", ($value[1] ?? ""));
        // get the users from the provided facultyNums
        addUsers($db, $facultyNums, $usernames);

        $name = $value[2] ?? "";
        $description = $value[3] ?? "";
        $author = $facultyNums[0];
        $date = date('Y-m-d H:i:s');
        
        $project = new Project($name, $description, $date, $author);
        if (!$project->create($db)) // the project has already been created beforehand
            continue;
        
        $date = date('Y-m-d H:i:s');
        $notifier = new Notifier($db, $facultyNums[0]);
        $notifier->addNotification("Нов проект е създаден: " . $name, $date, array_slice($facultyNums, 1));
        foreach ($facultyNums as $fn) {
            $project->addCollaborator($db, $fn);
            $isSuccesful = true;
        }
        
        addRequirementToDBFromTSVRead($db, $project, 1, $value, $author, "crucial");
        addRequirementToDBFromTSVRead($db, $project, 2, $value, $author, "crucial");
        addRequirementToDBFromTSVRead($db, $project, 3, $value, $author, "crucial");
        
    }
    
    appendNotificationForSuccesfulImporting($db, $isSuccesful);
    switchURL($isSuccesful);
}

function handleTeachersFormatWrapper($db, $tabTextArray) {
    $values = handleTeachersFormat($tabTextArray);
    addCsvValuesToDB($values, $db);
}

function parseRequirementColumn($requirementComponents, $index) {
    if ($requirementComponents[$index] !== null && $requirementComponents[$index] !== "")
        return $requirementComponents[$index];
    else
        return null;
}


function handleRawRequirements($db, $rawRequirementsArray, $REQUIREMENTS_FIELDS_SEPARATOR, $author, $project) {
    foreach ($rawRequirementsArray as $rawRequirement) {
        // it is a string, split it into fields and see what parameters can be retrieved.
        $components = explode($REQUIREMENTS_FIELDS_SEPARATOR, $rawRequirement);
        
        $heading = parseRequirementColumn($components, 0);
        $description = parseRequirementColumn($components, 1);
        $type = parseRequirementColumn($components, 2);
        $impact = parseRequirementColumn($components, 3);

        $requirement = null;
        if ($type !== null && $type === 1) { // it is a non-functional requirement
            $metricName = parseRequirementColumn($components, 4);
            $metricValue = parseRequirementColumn($components, 5);
            $acceptance_criteria = parseRequirementColumn($components, 6);
            $requirement = new NonFunctionalRequirement($heading, $description, $impact, $acceptance_criteria, $metricName, $metricValue, $author);
        }
        else { // create a functional requirement
            $requirement = new FunctionalRequirement($heading, $description, $impact, $author);
        }

        $requirement->addRequirementToDB($db);
        $project->linkRequirementToProject($db, $requirement);
    }
}

// our format doens't allow for the creation of users! (the initial setup needs to happen with the teachers format)
// it can create project based on the users in the DB and append requirements to them!
function handleOurFormat($tabTextArray, $db) {
    $SEPARATOR = "\t";
    $REQUIREMENTS_SEPARATOR = "<|>";
    $REQUIREMENTS_FIELDS_SEPARATOR = "_|_";

    $tabTextArraySplit = [];
    array_shift($tabTextArray);
    $isSuccesful = false;
    //Автор	Останали факултетни номера	Тема	Описание и примерно разпределение	Изисквания (отделните параметри на всяко изискване са разделени с _|_, като отделните изисквания са разделени с <|>)
    foreach ($tabTextArray as $row) {
        $rowAsTSVArray = str_getcsv($row, $SEPARATOR);
        
        $author = $rowAsTSVArray[0];
        $otherMembers = [];
        if ($rowAsTSVArray[1] !== null && $rowAsTSVArray[1] !== "")
            $otherMembers = explode($REQUIREMENTS_FIELDS_SEPARATOR, $rowAsTSVArray[1]);
        
        // check if all members are in the DB -> if not then skip this project
        $allMembers = array_slice($otherMembers, 0);
        array_push($allMembers, $author);
        if (!areAllMembersPresentInTheDB($allMembers, $db))
            continue;

        // check if the user is among them -> if not then skip this project (admin can continue)
        if ($_SESSION['username'] !== "admin" && !array_search($_SESSION['username'], $allMembers, true))
            continue;

        $heading = $rowAsTSVArray[2];
        $description = $rowAsTSVArray[3];
        $date = date('Y-m-d H:i:s');
        $project = new Project($heading, $description, $date, $author);
        if (!$project->create($db)) // the project has already been created beforehand
            continue;

        $notifier = new Notifier($db, $author);
        $notifier->addNotification("Нов проект е създаден: " . $heading, $date, $otherMembers);
        foreach ($allMembers as $fn) {
            $project->addCollaborator($db, $fn);
            $isSuccesful = true;
        }

        // create and link the requirements
        $rawRequirementsArray = null;
        if ($rowAsTSVArray[4] !== null && $rowAsTSVArray[4] !== "") {
            $rawRequirementsArray = explode($REQUIREMENTS_SEPARATOR, $rowAsTSVArray[4]);
            handleRawRequirements($db, $rawRequirementsArray, $REQUIREMENTS_FIELDS_SEPARATOR, $author, $project);
        }
    }

    appendNotificationForSuccesfulImporting($db, $isSuccesful);
    switchURL($isSuccesful);
}

$db = new Database();
$values = handleInput($db);
?>
