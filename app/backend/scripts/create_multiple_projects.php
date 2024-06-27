<?php
namespace App\Backend\Scripts;
require_once __DIR__ . '/../../../vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

use App\Backend\Classes\Database;
use App\Backend\Classes\Project;
use App\Backend\Classes\Notifier;

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


// 2 variants for file format -> 4 cols (our supported) or teachers format
function handleInput() {
    $tabText = $_POST['tabText'] ?? '';
    $tabTextArray = explode("\n", $tabText);
    // TODO - error message for no rows
    if (count(str_getcsv($tabTextArray[0], "\t")) > 4) {
        return handleTeachersFormat($tabTextArray);
    }
    else {
        return handleOurFormat($tabTextArray);
    }
}

function handleTeachersFormat($tabTextArray) {
    $tabTextArraySplit = [];
    $tabTextArrayLen = count($tabTextArray);
    for ($rowIndex = 0; $rowIndex < $tabTextArrayLen; $rowIndex++) {
        $row = $tabTextArray[$rowIndex];
        $csvArray = str_getcsv($row, "\t");
        
        // if there are any students, on this project
        $ADMINISTRATIVE_ROWS_COUNT = 3;
        if (strlen($csvArray[1]) !== 0) {
            if ($rowIndex <= $ADMINISTRATIVE_ROWS_COUNT - 1) { // it is header row or example row, skip them!
                continue;
            }
            if (!isset($csvArray[2]) || $csvArray[2] === null || $csvArray[2] === "") // it is empty, skip the line
                continue;
    
            $requirements = $csvArray[7] ?? "";
            if (isset($csvArray[8]) && strlen($csvArray[8]) > 0)
            $requirements = $requirements . ',' . $csvArray[8];
            if (isset($csvArray[9]) && strlen($csvArray[9]) > 0)
            $requirements = $requirements . ',' . $csvArray[9];

            $interestingCsvFields = [$csvArray[2], $csvArray[5], $csvArray[6], $requirements];
            // split by valid fields on ; . Note that a ; can be trailling!
            $members = explode(";", $csvArray[2]);
            $teams = explode(";", $csvArray[1]);
            $teamsCount = count($teams);
            for ($teamIndex = 0; $teamIndex < $teamsCount; $teamIndex++) {
                if ($teams[$teamIndex] !== null && strlen($teams[$teamIndex]) !== 0) {
                    $formatedMembers = trim($members[$teamIndex]);
                    $formatedMembers = preg_replace('/\s+/', " ", $formatedMembers); // remove multiple spaces inside the name

                    $interestingCsvFields[0] = $formatedMembers;
                    $tabTextArraySplit[] = $interestingCsvFields;
                }
            }
    
        }
    }
    return $tabTextArraySplit;
}

function handleOurFormat($tabTextArray) {
    $tabTextArraySplit = [];
    $tabTextArrayLen = count($tabTextArray);
    echo "<pre>";
    echo "We have to traverse $tabTextArrayLen rows!";
    echo "</pre>";
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

function addCsvValuesToDB($csvValues, $db) {
    $isSuccesful = false;
    $notifier = new Notifier($db);

    foreach ($csvValues as $value) {
        // collaborators, name, description, initial_requirements
        $project = new Project($db);
    
        $collaborators = $value[0] ?? "";
        $name = $value[1] ?? "";
        $description = $value[2] ?? "";
        $requirements = $value[3] ?? "";
        $project->setProjectDetails($name, $description, $collaborators, $requirements);
    
        if ($project->create()) {
            $notifier->addNotification("Нов проект е създаден: " . $name);
            $isSuccesful = true;
        }
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
