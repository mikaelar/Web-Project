<?php
namespace App;
use InvalidArgumentException;
use DomainException;

class Comment {
    function __construct($id, $author, $date, $content)
    {
        // TODO implement our wrapper class of $date in order to serve as a facade - keep dates only in BG format.
        $this->setID($id);
        $this->setAuthor($author);
        $this->setDate($date);
        $this->setContent($content);
    }

    public function getID() 
    {
        return $this->id;
    }

    public function getAuthor() 
    {
        return $this->author;
    }

    public function getDate() 
    {
        return $this->date;
    }

    public function getContent() 
    {
        return $this->content;
    }

    public function equals($other)
    {
        if ($other === null)
            return false;
        elseif (!$other instanceof Comment)
            return false;
        else
            return $other->getID() === $this->id;
    }

    public static $FIELD_INVALID_MESSAGE = 'Field %s of Comment class cannot be %s!';
    public static $EMPTY_STRING = "";

    private function setID($id)
    {
        if ($id === null)
            throw new InvalidArgumentException(sprintf(Comment::$FIELD_INVALID_MESSAGE, "id", "null"), 1);
        $this->id = $id;
    }

    private function setAuthor($author)
    {
        if ($author === null)
            throw new InvalidArgumentException(sprintf(Comment::$FIELD_INVALID_MESSAGE, "author", "null"), 1);
        if ($author === Comment::$EMPTY_STRING)
            throw new DomainException(sprintf(Comment::$FIELD_INVALID_MESSAGE, "author", "empty"), 2);
        $this->author = $author;
    }

    private function setDate($date)
    {
        // it should be checked that it is valid beforehand! - our class Date will just wrap some functionalities of the PHP date
        if ($date === null)
            throw new InvalidArgumentException(sprintf(Comment::$FIELD_INVALID_MESSAGE, "date", "null"), 1);
        $this->date = $date;
    }

    private function setContent($content)
    {
        if ($content === null)
            throw new InvalidArgumentException(sprintf(Comment::$FIELD_INVALID_MESSAGE, "content", "null"), 1);
        if ($content === Comment::$EMPTY_STRING)
            throw new DomainException(sprintf(Comment::$FIELD_INVALID_MESSAGE, "content", "empty"), 2);
        $this->content = $content;
    }

    // Should there be content revision or not - will see as the project goes on
    private $id;
    private $author;
    private $date;
    private $content;
}

?>