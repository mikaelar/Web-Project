<?php
namespace App\Backend\Classes;

use App\Backend\Classes\Database;

class User {
    private $conn;
    private $facultyNum;
    private $username;
    private $email;
    private $password;
    private $authenticated = false;

    public function __construct($db) {
        $this->conn = $db->getConnection();
    }

    public function setUserDetails($facultyNum, $username, $password, $email = null) {
        $this->setFN($facultyNum);
        $this->setUsernameForCreation($username);
        $this->setEmail($email);
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function register() {
        if (!$this->areAllMandatoryFieldsFilled()) {
            header("refresh:2;url=../../../frontend/login_register/register.html");
            return;
        }

        $stmt = $this->conn->prepare("INSERT INTO users (facultyNum, username, password, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $this->facultyNum, $this->username, $this->password, $this->email);

        if ($stmt->execute()) {
            echo "Вие се регистрирахте успешно.";
            header("refresh:2;url=../../../frontend/login_register/login.html");
        } else {
            echo "Error: " . $stmt->error;
            header("refresh:2;url=../../../frontend/login_register/register.html");
        }

        $stmt->close();
    }
    
    public function setUsername($username) {
        $this->username = $username;
    }


    public function setUsernameForCreation($username) {
        if (!$this->checkIfUserWithParameterExists("username", $username)) {
            $this->username = $username;
        } else
            $this->username = null;
    }

    public function setEmail($email) {
        if (!$this->checkIfUserWithParameterExists("email", $email)) {
            $this->email = $email;
        } else 
            $this->email = null;
    }

    public function authenticate($password) {
        $stmt = $this->conn->prepare("SELECT username, password, facultyNum FROM users WHERE username = ?");
        $stmt->bind_param("s", $this->username);

        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($username, $hashed_password, $facultyNum);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $this->authenticated = true;
                $_SESSION['username'] = $username;
                $_SESSION['facultyNum'] = $facultyNum;
                header("refresh:1;url=../../../frontend/manage_homepage/homepage.php");
                exit();
            } else {
                echo "Грешна парола! Моля опитай пак.";
                header("refresh:2;url=../../../frontend/login_register/login.html");
                exit();
            }
        } else {
            echo "Потребителят не е намерен. Моля опитай пак.";
            header("refresh:2;url=../../../frontend/login_register/login.html");
        }

        $stmt->close();
    }

    public function isAuthenticated() {
        return $this->authenticated;
    }

    public function changePassword($current_password, $new_password) {
        if ($current_password === $new_password)
            return;

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
                    header("refresh:2;url=../../../frontend/login_register/login.html");
                    exit();
                } else {
                    echo '<div style="text-align: center; font-size: 24px; margin-top: 20px;">Възникна грешка при промяната на паролата. Препращане към страницата за вход в системата.</div>';
                    header("refresh:2;url=../../../frontend/login_register/login.html");
                    exit();
                }

                $update_stmt->close();
            } else {
                echo '<div style="text-align: center; font-size: 24px; margin-top: 20px;">Грешна текуща парола! Препращане към страницата за вход в системата.</div>';
                header("refresh:2;url=../../../frontend/login_register/login.html");
                exit();
            }
        } else {
            echo '<div style="text-align: center; font-size: 24px; margin-top: 20px;">Потребителят не е намерен. Препращане към страницата за регистрация в системата.</div>';
            header("refresh:2;url=../../../frontend/login_register/register.html");
            exit();
        }

        $stmt->close();
    }

    private function setFN($facultyNum) {
        if (!$this->checkIfUserWithParameterExists("facultyNum", $facultyNum)) {
            $this->facultyNum = $facultyNum;
        } else
            $this->facultyNum = null;
    }

    private function checkIfUserWithParameterExists($parameterName, $parameter) {
        $validColumns = ['username', 'email', 'facultyNum']; // Add other valid column names as needed
        if (!in_array($parameterName, $validColumns)) {
            throw new Exception("Invalid parameter name: $parameterName");
        }

        $stmt = $this->conn->prepare("SELECT $parameterName FROM users WHERE $parameterName = ?");
        $stmt->bind_param("s", $parameter);
        $stmt->execute();
        $stmt->store_result();
        // a user with the desired username already exists
        $result = false;
        if ($stmt->num_rows > 0) {
            echo "Потребител с поле $parameterName $parameter вече съществува в БД!\n";
            $result = true;
        }

        $stmt->close();
        return $result;
    }

    private function areAllMandatoryFieldsFilled() {
        return $this->facultyNum != null && $this->password != null && $this->username != null;
    }
}
?>