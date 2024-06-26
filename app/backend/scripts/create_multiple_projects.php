<?php
namespace App\Backend\Scripts;
require_once __DIR__ . '/../../../vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

use App\Backend\Classes\Database;
use App\Backend\Classes\Project;
use App\Backend\Classes\Notifier;

// parse the CSV or the Tabs and execute with for the code below (it will be very slow but technical debt was already taken)

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "form";

$db = new Database($servername, $dbusername, $dbpassword, $dbname);

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


$tabText = $_POST['tabText'] ?? '';
$tabTextArray = explode("\n", $tabText);
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

        // split by valid fields on ; . Note that a ; can be trailling!
        $members = explode(";", $csvArray[2]);
        $teams = explode(";", $csvArray[1]);
        $teamsCount = count($teams);
        for ($teamIndex = 0; $teamIndex < $teamsCount; $teamIndex++) {
            if ($teams[$teamIndex] !== null && strlen($teams[$teamIndex]) !== 0) {
                $teamInfo = [$csvArray[0], trim($teams[$teamIndex]), trim($members[$teamIndex]), ...(array_slice($csvArray, 3))];
                $tabTextArraySplit[] = $teamInfo;
            }
        }

    }
}

$isSuccesful = false;
foreach ($tabTextArraySplit as $value) {
    // name, description, collaborators, initial_requirements
    $notifier = new Notifier($db);
    $project = new Project($db);

    $name = $value[5] ?? "";
    $description = $value[6] ?? "";
    $collaborators = $value[2] ?? "";

    $requirements = $value[7] ?? "";
    if (isset($value[8]) && strlen($value[8]) > 0)
        $requirements = $requirements . ',' . $value[8];
    if (isset($value[9]) && strlen($value[9]) > 0)
        $requirements = $requirements . ',' . $value[9];

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
?>
