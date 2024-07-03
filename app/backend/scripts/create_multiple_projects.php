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

// parse the CSV or the Tabs and execute with for the code below (it will be very slow but technical debt was already taken)

$db = new Database();

// echo '<pre>';
// echo("No error yet");
// echo '</pre>';

// const FILENAME = 'csvFile';
// echo "<pre>";
// echo implode('\n', $_FILES);
// echo "</pre>";
// if (isset($_FILES['csvFile']['tmp_name']) && $_FILES[FILENAME]['error'] === UPLOAD_ERR_OK) {
//     $fileObj = $_FILES[FILENAME];
//     $fileTmpPath = $fileObj['tmp_name'];
//     $fileName = $fileObj['name'];
//     $fileParts = explode('.', $fileName);
//     $fileExtension = strtolower(end($fileParts));

//     echo '<pre>';
//     echo "File extension: $fileExtension\n";
//     echo "File tmp path: $fileTmpPath\n";
//     echo "File name: $fileName\n";
//     echo '</pre>';

//     if ($fileExtension === 'csv') {
//         $csvData = file_get_contents($fileTmpPath);
//         $rows = array_map('str_getcsv', explode("\n", $csvData));
//         // Process CSV data here
//         echo '<pre>';
//         print_r($rows);
//         echo '</pre>';
//     } else {
//         echo 'Invalid file format!';
//     }
// } else {
//     echo '<pre>';
//     echo "No file uploaded or error occurred.\n";
//     if (isset($_FILES[FILENAME])) {
//         echo "Error code: " . $_FILES[FILENAME]['error'] . "\n";
//     } else {
//         echo "No \$_FILES entry found.\n";
//     }
//     echo '</pre>';
// }


// 2 variants for file format -> 5 cols (our supported) or teachers format
function handleInput() {
    $tabText = $_POST['tabText'] ?? '';
    $tabTextArray = explode("\n", $tabText);
    // TODO - error message for no rows
    if (count(str_getcsv($tabTextArray[0], "\t")) > 5) {
        return handleTeachersFormat($tabTextArray);
    }
    else {
        return handleOurFormat($tabTextArray);
    }
}

// remake our format to include FN-s, teachers also
// all columns + FN -> FNs, names, title, description, requirements
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
                    //echo "Екип от $formattedTeam с фн-та $formattedMembers\n";
                    //echo "Изискване 1: $csvArray[7]";
                    //echo "Изискване 2: $csvArray[8]";
                    //echo "Изискване 3: $csvArray[9]";
                    // Second team seems to have no requirements!
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

// TODO remake our format to have an extra field of fn-s
function handleOurFormat($tabTextArray) {
    $tabTextArraySplit = [];
    $tabTextArrayLen = count($tabTextArray);
    //echo "<pre>";
    //echo "We have to traverse $tabTextArrayLen rows!";
    //echo "</pre>";
    for ($rowIndex = 0; $rowIndex < $tabTextArrayLen; $rowIndex++) {
        $row = $tabTextArray[$rowIndex];
        $csvArray = str_getcsv($row, "\t");
        
        // if there are any students, on this project
        $ADMINISTRATIVE_ROWS_COUNT = 1;
        if (strlen($csvArray[0]) !== 0) {
            if ($rowIndex <= $ADMINISTRATIVE_ROWS_COUNT - 1) { // it is header row or example row, skip them!
                continue;
            }
            if (!isset($csvArray[0]) || $csvArray[0] === null || $csvArray[0] === "") // it is empty, skip the line
                continue;
    
            // split by valid fields on ; . Note that a ; can be trailling!
            $members = explode(";", $csvArray[0]);
            $membersCount = count($members);
            for ($membersIndex = 0; $membersIndex < $membersCount; $membersIndex++) {
                if ($members[$membersIndex] !== null && strlen($members[$membersIndex]) !== 0) {
                    $formatedMembers = trim($members[$membersIndex]);
                    $formatedMembers = preg_replace('/\s+/', " ", $formatedMembers); // remove multiple spaces inside the name

                    $csvArray[0] = $formatedMembers;
                    $tabTextArraySplit[] = $csvArray;
                }
            }
    
        }
    }
    return $tabTextArraySplit;
}

function addUsers($db, $facultyNums, $usernames) {
    // the placeholders are tricky as their length varies, that changes the query statement
    $facultyNumsCount = count($facultyNums);
    
    $placeholders = implode(',', array_fill(0, $facultyNumsCount, '?')); // generate as many placeholders as fn-s to check
    $query = "SELECT facultyNum, username from users WHERE facultyNum IN ($placeholders)";
    $stmt = $db->getConnection()->prepare($query);
    
    $types = str_repeat("s", $facultyNumsCount);
    $bindParams = [];
    foreach ($facultyNums as $key => $value) {
        $bindParams[$key] = &$facultyNums[$key];
    }

    // NB - bind param doesn't accept array spreading, so we need a work around
    call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $bindParams));
    $stmt->execute();
    $stmt->bind_result($fetchFN, $fetchUsername);

    $existingFacultyNums = [];
    $existingUsernames = [];
    while ($stmt->fetch()) {
        $existingFacultyNums[] = $fetchFN;
        $existingUsernames[] = $fetchUsername;
    }

    $missingFacultyNums = [];
    $missingUsernames = [];
    foreach ($facultyNums as $key => $value) {
        // if the value is not found, then add the missing user to the list
        if (!array_search($value, $existingFacultyNums, true)) {
            $missingFacultyNums[] = $value;
            $missingUsernames[] = $usernames[$key];
        }
    }

    // for each missing user, create him
    foreach ($missingFacultyNums as $key => $missingFN) {
        $user = new User($db);
        $DEFAULT_PASSWORD = $missingFN;
        $user->setUserDetails($missingFN, $missingUsernames[$key], $DEFAULT_PASSWORD);
        $user->registerWithoutSwappingURLs();
    }

    $stmt->close();
}

function addCsvValuesToDB($csvValues, $db) {
    $isSuccesful = false;

    foreach ($csvValues as $value) {
        // FORMAT of value (arr) : facultyNums, usernames, name, description, req1 (can be empty), req2?, req3?
    
        $facultyNums = explode(", ", ($value[0] ?? ""));
        $usernames = explode(", ", ($value[1] ?? ""));
        // get the users from the provided facultyNums
        addUsers($db, $facultyNums, $usernames);

        $name = $value[2] ?? "";
        $description = $value[3] ?? "";
        $author = $facultyNums[0];
        $date = date('Y-m-d H:i:s');
        
        $project = new Project($name, $description, $date, $author);
        $project->create($db);
        
        $date = date('Y-m-d H:i:s');
        $notifier = new Notifier($db, $facultyNums[0]);
        $notifier->addNotification("Нов проект е създаден: " . $name, $date, array_slice($facultyNums, 1));
        foreach ($facultyNums as $fn) {
            $project->addCollaborator($db, $fn);
            $isSuccesful = true;
        }
        
        $arrLen = count($value);
        echo "$arrLen";
        echo "Стойности за изисквания $value[4], $value[5], $value[6]";

        $firstRequirementDescription = $value[4] ?? "";
        $firstRequirement = new FunctionalRequirement("Изисквания към участник 1", $firstRequirementDescription, "crucial", $facultyNums[0]);
        $firstRequirement->addRequirementToDB($db);
        $project->linkRequirementToProject($db, $firstRequirement);

        // create a requirement, add an author to it, add it to the DB and link it to the project

        $secondRequirementDescription = $value[5] ?? "";
        $secondRequirement = new FunctionalRequirement("Изисквания към участник 2", $secondRequirementDescription, "crucial", $facultyNums[0]);
        $secondRequirement->addRequirementToDB($db);
        $project->linkRequirementToProject($db, $secondRequirement);

        $thirdRequirementDescription = $value[6] ?? "";
        $thirdRequirement = new FunctionalRequirement("Изисквания към участник 3", $thirdRequirementDescription, "crucial", $facultyNums[0]);
        $thirdRequirement->addRequirementToDB($db);
        $project->linkRequirementToProject($db, $thirdRequirement);
        // TODO check if it works
    }
    
    if ($isSuccesful) {
        header("Location: ../../frontend/manage_homepage/homepage.php");
    } else {
        header("Location: ../../frontend/create_project/add_multiple_projects.html");
    }
}


$values = handleInput();
addCsvValuesToDB($values, $db);

?>
