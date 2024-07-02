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
        $this->setUsername($username);
        $this->setEmail($email);
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function register() {
        if (!$this->areAllMandatoryFieldsFilled()) {
            echo "Not all mandatory fields have been filled. Currently you have:";
            echo "Faculty num $this->facultyNum \n Username $this->username";
            if ($this->password === null) {
                echo "Missing password";
            }
            else
                echo "Valid password";
            return;
        }

        $stmt = $this->conn->prepare("INSERT INTO users (facultyNum, username, password, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $this->facultyNum, $this->username, $this->email, $this->password);

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
        if (!$this->checkIfUserWithParameterExists("username", $username)) {
            echo "Managed to set username";
            $this->username = $username;
        }
    }

    public function setEmail($email) {
        if (!$this->checkIfUserWithParameterExists("email", $email)) {
            echo "Managed to set Mail";
            $this->email = $email;
        }
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
            echo "Managed to set FN";
        }
    }

    private function checkIfUserWithParameterExists($parameterName, $parameter) {
        $stmt = $this->conn->prepare("SELECT ? FROM users WHERE ? = ?");
        $stmt->bind_param("sss", $parameterName, $parameterName, $parameter);
        $stmt->execute();
        // a user with the desired username already exists
        $result = false;
        if ($stmt->fetch()) {
            echo "User with $parameterName $parameter already exists in the DB!";
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