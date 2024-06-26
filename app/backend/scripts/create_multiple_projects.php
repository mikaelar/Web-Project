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
foreach ($tabTextArray as $value) {
    $csvArray = str_getcsv($value, "\t");
    echo "<pre>";
    echo implode("|", $csvArray);
    echo "</pre>";
    // if there are any students, on this project
    if (strlen($csvArray[1]) !== 0) {
        $tabTextArraySplit[] = $csvArray;
    }
}

foreach ($tabTextArraySplit as $value) {
    // name, description, collaborators, initial_requirements
    $notifier = new Notifier($db);
    $project = new Project($db);

    $name = $value[4] ?? "";
    $description = $value[5] ?? "";
    $collaborators = $value[2] ?? "";

    $requirements = $value[6] ?? "";
    if (isset($value[7]) && strlen($value[7]) > 0)
        $requirements = $requirements . ',' . $value[7];
    if (isset($value[8]) && strlen($value[8]) > 0)
        $requirements = $requirements . ',' . $value[8];

    $project->setProjectDetails($name, $description, $collaborators, $requirements);

    if ($project->create()) {
    $notifier->addNotification("Нов проект е създаден: " . $name);
    header("Location: ../../frontend/manage_homepage/homepage.php");
    } else {
        header("Location: ../../frontend/create_project/create_project.html");
    }
}
?>
