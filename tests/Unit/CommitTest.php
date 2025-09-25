<?php

namespace johninamillion\Git\Tests\Unit;

use DateTimeImmutable;
use johninamillion\Git\Commit;
use johninamillion\Git\Tests\TestCase;
use johninamillion\Git\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Commit Test.
 *
 * @package johninamillion/php-github
 * @covers  Commit
 */
final class CommitTest extends TestCase
{
    #[Test]
    public function it_returns_hash_and_short_hash()
    {
        $user = $this->createMock(User::class);
        $commit = new Commit($user, '2025-09-25 12:00:00', 'abcdef1234567890', 'Initial commit');

        $this->assertSame('abcdef1234567890', $commit->getHash());
        $this->assertSame('abcdef1', $commit->shortHash());
    }

    #[Test]
    public function it_returns_author()
    {
        $user = $this->createMock(User::class);
        $commit = new Commit($user, '2025-09-25 12:00:00', 'abcdef1234567890', 'Initial commit');

        $this->assertSame($user, $commit->getAuthor());
    }

    #[Test]
    public function it_returns_message()
    {
        $user = $this->createMock(User::class);
        $commit = new Commit($user, '2025-09-25 12:00:00', 'abcdef1234567890', 'Fix bug #123');

        $this->assertSame('Fix bug #123', $commit->getMessage());
    }

    #[Test]
    public function it_returns_date_as_datetime_immutable(): void
    {
        $user = $this->createMock(User::class);
        $dateString = '2025-09-25 12:00:00';
        $commit = new Commit($user, $dateString, 'abcdef1234567890', 'Some message');

        $date = $commit->getDate();

        $this->assertInstanceOf(DateTimeImmutable::class, $date);
        $this->assertSame($dateString, $date->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_correctly_identifies_merge_commits()
    {
        $user = $this->createMock(User::class);

        $mergeCommit = new Commit($user, '2025-09-25', 'abcdef1234567', 'Merge branch feature-x');
        $normalCommit = new Commit($user, '2025-09-25', 'abcdef1234567', 'Fix issue');

        $this->assertTrue($mergeCommit->isMergeCommit());
        $this->assertFalse($normalCommit->isMergeCommit());
    }

    #[Test]
    public function short_hash_is_7_characters()
    {
        $user = $this->createMock(User::class);
        $commit = new Commit($user, '2025-09-25', '123456789abcdef', 'Test message');

        $this->assertSame('1234567', $commit->shortHash());
    }
}
