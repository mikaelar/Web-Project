<?php
namespace App\Backend\Scripts;
require_once __DIR__ . '/../../../vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

$db = new Database();

// Обработка на POST заявки
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['userStoryForm'])) {
        $formAction = $_POST['formAction'];
        
        switch ($formAction) {
            case 'add':
                addUserStory($conn);
                break;
            case 'edit':
                editUserStory($conn);
                break;
            case 'remove':
                removeUserStory($conn);
                break;
            default:
                echo "Invalid form action.";
                break;
        }
    }
}

// Функция за добавяне на User Story
function addUserStory($conn) {
    $title = $_POST['userStoryTitle'] ?? '';
    $description = $_POST['userStoryDescription'] ?? '';

    if (!empty($title) && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO userstories (title, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $description);

        if ($stmt->execute()) {
            // Пренасочване към формуляра след успешно добавяне
            header("Location: ../../frontend/user_stories/UserStories.html");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Title and Description are required.";
    }
}

// Функция за редактиране на User Story
function editUserStory($conn) {
    $selectedStoryId = $_POST['editUserStoryId'] ?? '';
    $newTitle = $_POST['editUserStoryTitle'] ?? '';
    $newDescription = $_POST['editUserStoryDescription'] ?? '';

    if (!empty($selectedStoryId) && !empty($newTitle) && !empty($newDescription)) {
        $stmt = $conn->prepare("UPDATE userstories SET title = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $newTitle, $newDescription, $selectedStoryId);

        if ($stmt->execute()) {
            // Пренасочване към формуляра след успешно редактиране
            header("Location: ../../frontend/user_stories/UserStories.html");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "All fields are required for editing.";
    }
}

// Функция за премахване на User Story
function removeUserStory($conn) {
    $selectedStoryId = $_POST['removeUserStoryId'] ?? '';

    if (!empty($selectedStoryId)) {
        $stmt = $conn->prepare("DELETE FROM userstories WHERE id = ?");
        $stmt->bind_param("i", $selectedStoryId);

        if ($stmt->execute()) {
            // Пренасочване към формуляра след успешно премахване
            header("Location: ../../frontend/user_stories/UserStories.html");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Story ID is required for removal.";
    }
}

$conn->close();
?>
