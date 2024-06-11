<?php
namespace App;

use InvalidArgumentException;
use DomainException;

class UserStory {
    private $id;
    private $title;
    private $description;
    private $date;
    private $author;

    public function __construct($id, $title, $description, $date, $author) {
        $this->setID($id);
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setDate($date);
        $this->setAuthor($author);
    }

    public function getID() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDate() {
        return $this->date;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function equals($other) {
        if ($other === null) {
            return false;
        } elseif (!$other instanceof UserStory) {
            return false;
        } else {
            return $other->getID() === $this->id;
        }
    }

    public static $FIELD_INVALID_MESSAGE = 'Field %s of UserStory class cannot be %s!';
    public static $EMPTY_STRING = "";

    private function setID($id) {
        if ($id === null) {
            throw new InvalidArgumentException(sprintf(UserStory::$FIELD_INVALID_MESSAGE, "id", "null"), 1);
        }
        $this->id = $id;
    }

    private function setTitle($title) {
        if ($title === null) {
            throw new InvalidArgumentException(sprintf(UserStory::$FIELD_INVALID_MESSAGE, "title", "null"), 1);
        }
        if ($title === UserStory::$EMPTY_STRING) {
            throw new DomainException(sprintf(UserStory::$FIELD_INVALID_MESSAGE, "title", "empty"), 2);
        }
        $this->title = $title;
    }

    private function setDescription($description) {
        if ($description === null) {
            throw new InvalidArgumentException(sprintf(UserStory::$FIELD_INVALID_MESSAGE, "description", "null"), 1);
        }
        if ($description === UserStory::$EMPTY_STRING) {
            throw new DomainException(sprintf(UserStory::$FIELD_INVALID_MESSAGE, "description", "empty"), 2);
        }
        $this->description = $description;
    }

    private function setDate($date) {
        if ($date === null) {
            throw new InvalidArgumentException(sprintf(UserStory::$FIELD_INVALID_MESSAGE, "date", "null"), 1);
        }
        // Optionally, validate the date format here.
        $this->date = $date;
    }

    private function setAuthor($author) {
        if ($author === null) {
            throw new InvalidArgumentException(sprintf(UserStory::$FIELD_INVALID_MESSAGE, "author", "null"), 1);
        }
        if ($author === UserStory::$EMPTY_STRING) {
            throw new DomainException(sprintf(UserStory::$FIELD_INVALID_MESSAGE, "author", "empty"), 2);
        }
        $this->author = $author;
    }
}
?>
