<?php
namespace App\Backend\Classes;

use App\Backend\Classes\Database;

class User {
    private $conn;
    private $username;
    private $email;
    private $password;
    private $authenticated = false;

    public function __construct($db) {
        $this->conn = $db->getConnection();
    }

    public function setUserDetails($username, $email, $password) {
        $this->username = $username;
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function register() {
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $this->username, $this->email, $this->password);

        if ($stmt->execute()) {
            echo "Вие се регистрирахте успешно.";
            header("refresh:2;url=login.html");
        } else {
            echo "Error: " . $stmt->error;
            header("refresh:2;url=register.html");
        }

        $stmt->close();
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function authenticate($password) {
        $stmt = $this->conn->prepare("SELECT username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $this->username);

        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($username, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $this->authenticated = true;
                $_SESSION['username'] = $username;
                header("refresh:1;url=../manage_homepage/homepage.php");
                exit();
            } else {
                echo "Грешна парола! Моля опитай пак.";
                header("refresh:2;url=login.html");
                exit();
            }
        } else {
            echo "Потребителят не е намерен. Моля опитай пак.";
            header("refresh:2;url=login.html");
        }

        $stmt->close();
    }

    public function isAuthenticated() {
        return $this->authenticated;
    }

    public function changePassword($current_password, $new_password) {
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $this->username);

        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();

            if (password_verify($current_password, $hashed_password)) {
                $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

                $update_stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                $update_stmt->bind_param("ss", $new_password_hashed, $this->username);

                if ($update_stmt->execute()) {
                    echo '<div style="text-align: center; font-size: 24px; margin-top: 20px;">Паролата е успешно променена! Препращане към страницата за вход в системата.</div>';
                    header("refresh:2;url=login.html");
                    exit();
                } else {
                    echo '<div style="text-align: center; font-size: 24px; margin-top: 20px;">Възникна грешка при промяната на паролата. Препращане към страницата за вход в системата.</div>';
                    header("refresh:2;url=login.html");
                    exit();
                }

                $update_stmt->close();
            } else {
                echo '<div style="text-align: center; font-size: 24px; margin-top: 20px;">Грешна текуща парола! Препращане към страницата за вход в системата.</div>';
                header("refresh:2;url=login.html");
                exit();
            }
        } else {
            echo '<div style="text-align: center; font-size: 24px; margin-top: 20px;">Потребителят не е намерен. Препращане към страницата за регистрация в системата.</div>';
            header("refresh:2;url=register.html");
            exit();
        }

        $stmt->close();
    }
}
?>