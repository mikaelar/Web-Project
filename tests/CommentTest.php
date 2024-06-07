<?php
namespace Tests;

use App\Comment;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase 
{
    const DEFAULT_ID = 0;
    const DEFAULT_AUTHOR = "Antoan";
    const CURRENT_UNIX_TIMESTAMP = 1717757422;
    public static function getCurrentDate()
    {
        return date("d-m-Y", self::CURRENT_UNIX_TIMESTAMP);
    }
    const DEFAULT_CONTENT = "Redundant comment, but I have to get salary!";

    #[Test]
    public function succesfulCommentCreation()
    {
        // given we have provided valid arguments
        // when we construct the object
        $comment = new Comment(self::DEFAULT_ID, self::DEFAULT_AUTHOR, self::getCurrentDate(), self::DEFAULT_CONTENT);

        // it will have valid a state
        $this->assertEquals(self::DEFAULT_ID, $comment->getID());
        $this->assertEquals( self::DEFAULT_AUTHOR, $comment->getAuthor());
        $this->assertEquals(self::getCurrentDate(), $comment->getDate());
        $this->assertEquals(self::DEFAULT_CONTENT, $comment->getContent());
    }

    // Check setters if each one throws when provided invalid arguments and check their messages

    #[Test]
    public function idNullThrowsInvalidArgument()
    {
        // given we provide an invalid (null) id, the it should throw
        $this->expectException(\InvalidArgumentException::class);
        $comment = new Comment(null, self::DEFAULT_AUTHOR, self::getCurrentDate(), self::DEFAULT_CONTENT);
        // with invalid message
        $message = 'Field id of Comment class cannot be null!';
        $this->expectExceptionMessage($message);
    }
    
    #[Test]
    public function authorNullThrowsInvalidArgument()
    {
        // given we provide an invalid (null) author, the it should throw
        $this->expectException(\InvalidArgumentException::class);
        $comment = new Comment(self::DEFAULT_ID, null, self::getCurrentDate(), self::DEFAULT_CONTENT);
        // with invalid message
        $message = 'Field author of Comment class cannot be null!';
        $this->expectExceptionMessage($message);
    }

    #[Test]
    public function authorEmptyThrowsDomainException()
    {
        // given we provide an empty author, the it should throw
        $this->expectException(\DomainException::class);
        $comment = new Comment(self::DEFAULT_ID, Comment::$EMPTY_STRING, self::getCurrentDate(), self::DEFAULT_CONTENT);
        // with invalid message
        $message = 'Field author of Comment class cannot be empty!';
        $this->expectExceptionMessage($message);
    }

    #[Test]
    public function dateNullThrowsInvalidArgument()
    {
        // given we provide an invalid (null) date, the it should throw
        $this->expectException(\InvalidArgumentException::class);
        $comment = new Comment(self::DEFAULT_ID, self::DEFAULT_AUTHOR, null, self::DEFAULT_CONTENT);
        // with invalid message
        $message = 'Field date of Comment class cannot be null!';
        $this->expectExceptionMessage($message);
    }

    #[Test]
    public function contentNullThrowsInvalidArgument()
    {
        // given we provide an invalid (null) content, the it should throw
        $this->expectException(\InvalidArgumentException::class);
        $comment = new Comment(self::DEFAULT_ID, self::DEFAULT_AUTHOR, self::getCurrentDate(), null);
        // with invalid message
        $message = 'Field content of Comment class cannot be null!';
        $this->expectExceptionMessage($message);
    }

    #[Test]
    public function contentEmptyThrowsDomainException()
    {
        // given we provide empty content, the it should throw
        $this->expectException(\DomainException::class);
        $comment = new Comment(self::DEFAULT_ID, self::DEFAULT_AUTHOR, self::getCurrentDate(), Comment::$EMPTY_STRING);
        // with invalid message
        $message = 'Field content of Comment class cannot be empty!';
        $this->expectExceptionMessage($message);
    }

    #[Test]
    public function checkCommentsAreSame()
    {
        // The checks are performed solely on ID, if 2 comments get the same id, although with different args then, 
        // they will be considered equal

        $comment = new Comment(self::DEFAULT_ID, self::DEFAULT_AUTHOR, self::getCurrentDate(), self::DEFAULT_CONTENT);
        $this->assertFalse($comment->equals(null));
        $this->assertFalse($comment->equals("Equivialent comment sitting here!"));

        $commentPassingByID = new Comment(self::DEFAULT_ID, "Mikaela", self::getCurrentDate(), "Victoria is Mika's favourite bride, while Antoan is the priest who brought them together in a group project"); // time to hate on my freestyle content
        $this->assertTrue($comment->equals($commentPassingByID));
        $this->assertNotSame($comment, $commentPassingByID); // they are not the same obj in memory
        
        $commentNotPassingAlthoughEverythingMatchesExceptID = new Comment(self::DEFAULT_ID + 1, self::DEFAULT_AUTHOR, self::getCurrentDate(), self::DEFAULT_CONTENT);
        $this->assertFalse($comment->equals($commentNotPassingAlthoughEverythingMatchesExceptID));
        $this->assertNotSame($comment, $commentNotPassingAlthoughEverythingMatchesExceptID);
    }
}

?>