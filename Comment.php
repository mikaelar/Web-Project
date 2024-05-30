<?php

class Comment {
    function __construct($id, $author, $date, $content)
    {
        // TODO check if injections in the constructor in the field date are better than none
        $this->id = $id;
        $this->author = $author;
        $this->date = $date;
        $this->content = $content;
    }

    public function getID() {
        return $this->id;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getDate() {
        return $this->date;
    }

    public function getContent() {
        return $this->content;
    }

    // Should there be content revision or not - will see as the project goes on
    private $id;
    private $author;
    private $date;
    private $content;
}

?>