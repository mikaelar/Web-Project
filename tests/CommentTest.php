<?php
namespace Tests;

// TODO find out how namespaces work and implement Composer
use App\Comment;
use PHPUnit\Framework\TestCase;
use App\Requirement\iRequirement;

class CommentTest extends TestCase 
{
    /** @test */
    public function succesfulCommentCreation()
    {
        // given we have provided valid arguments
        $ID = 0;
        $author = "Antoan";
        $date = date("d-m-Y");
        $content = "Stupid comment, but I have to get salary!";
        
        // when we construct the object
        $comment = new Comment($ID, $author, $date, $content);

        // it will have valid a state
        $this->assertEquals($ID, $comment->getID());
        $this->assertEquals($author, $comment->getAuthor());
        $this->assertEquals($date, $comment->getDate());
        $this->assertEquals($content, $comment->getContent());
    }

    // setters each one throws

//     /** @test */
//     public function throwsWhenIDIsNull()
//     {
//         // given that the author is null
//         // the function setID should throw
//         $this->expectException()
//     }
}

?>