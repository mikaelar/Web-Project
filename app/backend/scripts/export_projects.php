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
$sql = "SELECT * FROM projects";
$result = $db->getConnection()->query($sql);
$data = [["Имена", "Тема", "Описание и примерно разпределение", "Изисквания"]];
while($row = $result->fetch_assoc()) {
    $data[] = [$row["collaborators"], $row["name"], $row["description"], $row["initial_requirements"]];
}

$notifier = new Notifier($db);
$notifier->addNotification("Успешно експортирахте информацията на БД към .tsv формат");

header('Content-Type: text/tab-separated-values');
header('Content-Disposition: attachment;filename="exported_projects.tsv"');
$output = fopen('php://output', 'w');
$dataCount = count($data);
for ($i = 0; $i < $dataCount - 1; $i++) { 
    echo implode("\t", $data[$i]) . "\n";
}
echo implode("\t", $data[$dataCount - 1]);
fclose($output);
?>