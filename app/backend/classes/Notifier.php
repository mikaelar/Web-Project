<?php
namespace App\Backend\Classes;

use App\Backend\Classes\Database;

class Notifier {
    private $conn;
    private $facultyNum;

    public function __construct($db, $facultyNum) {
        $this->conn = $db->getConnection();
        $this->facultyNum = $facultyNum;
    }

    // TODO - check if it works for multiple people!
    public function addNotification($message, $date) {
        // create the base notification
        $query = "INSERT INTO notifications (message, date) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $message, $date);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            // get the id of newly created notification
            $notificationID = $stmt->insert_id;

            // add a row in the notifications for users table (relation many to many)
            $query = "INSERT INTO notifications_for_users (notifications_id, users_facultyNum, is_read) VALUES (?, ?, 0)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ss", $notificationID, $this->facultyNum);
            $stmt->execute();
            if ($stmt->affected_rows < 0) {
                echo "Настъпи проблем при добавянето на нотификация към спомагателната таблица с нотификации.";
            }
        } else {
            echo "Настъпи проблем при добавянето на нотификация към главната таблица нотификации.";
        }

        $stmt->close();
    }

    // check if it works for multiple people
    public function getNotifications() {
        $query = "SELECT id, message, date FROM notifications WHERE id IN (SELECT notifications_id FROM notifications_for_users WHERE users_facultyNum = ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $this->facultyNum);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $notifications;
    }

    // TODO - check for multiple people!
    public function markAsRead($id) {
        // remove the entry in the many to many table and also check if there are still entries left in it - if it was the final one, them remove it from the total table
        $query = "DELETE FROM notifications_for_users WHERE notifications_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // check if the deletion query went as planned
        if ($stmt->affected_rows > 0) {
            $query = "SELECT * FROM notifications_for_users WHERE notifications_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->store_result();
            
            // check if it was the last user, who hadn't read that message
            if ($stmt->num_rows === 0) {
                // if so, then delete the notification from the grand table
                $query = "DELETE FROM notifications WHERE id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();

                // check if the deletion query went as planned
                if ($stmt->affected_rows < 0) {
                    echo "Настъпи проблем при маркирането / изтриването на кортеж от същинското множество същности нотификации!";
                }
            } else {
                echo "Настъпи проблем при маркирането / изтриването на кортеж от таблицата много към много на нотификациите!";
            }

            $stmt->close();
        }
    }
}
?>
