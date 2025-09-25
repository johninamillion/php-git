<?php

namespace johninamillion\Git\Tests\Unit;

use johninamillion\Git\Commit;
use johninamillion\Git\Repository;
use johninamillion\Git\Tag;
use johninamillion\Git\Tests\TestCase;
use johninamillion\Git\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Repository Test.
 *
 * @package johninamillion/php-github
 * @covers  Repository
 */
final class RepositoryTest extends TestCase
{
    private Repository $repository;

    #[Test]
    public function it_returns_branch_or_fallback()
    {
        $this->repository = $this->getMockBuilder(Repository::class)
            ->onlyMethods(['runProcess'])
            ->getMock();


        $this->repository->expects($this->exactly(2))
            ->method('runProcess')
            ->with(['git', 'rev-parse', '--abbrev-ref', 'HEAD'])
            ->willReturnOnConsecutiveCalls('feature-branch', null);

        $this->assertSame('feature-branch', $this->repository->getBranch());
        $this->assertSame('master', $this->repository->getBranch());
    }

    #[Test]
    public function it_returns_array_of_changed_files()
    {
        $gitOutput = " M src/Foo.php\nA  src/Bar.php\n?? src/Baz.php\n";

        $this->repository->method('runProcess')
            ->with(['git', 'status', '--porcelain'], true)
            ->willReturnOnConsecutiveCalls($gitOutput);

        $expected = ['src/Foo.php', 'src/Bar.php', 'src/Baz.php'];

        $this->assertSame($expected, $this->repository->getChangedFiles());
    }

    #[Test]
    public function it_returns_empty_array_of_changed_files()
    {
        $this->repository->method('runProcess')
            ->with(['git', 'status', '--porcelain'], true)
            ->willReturnOnConsecutiveCalls(null);

        $this->assertSame([], $this->repository->getChangedFiles());
    }

    #[Test]
    public function it_returns_array_of_contributors()
    {
        $output = "  14\tJohn Doe <john@example.com>\n   7\tJane Smith <jane@example.com>";

        $this->repository->method('runProcess')
            ->with(['git', 'shortlog', '-sne'], true)
            ->willReturnOnConsecutiveCalls($output);

        $contributors = $this->repository->getContributors();

        $this->assertCount(2, $contributors);
        $this->assertInstanceOf(User::class, $contributors[0]);
        $this->assertSame('John Doe', $contributors[0]->getName());
        $this->assertSame('john@example.com', $contributors[0]->getEmail());
        $this->assertSame('Jane Smith', $contributors[1]->getName());
        $this->assertSame('jane@example.com', $contributors[1]->getEmail());
    }

    #[Test]
    public function it_returns_empty_array_of_contributors()
    {
        $this->repository->method('runProcess')
            ->with(['git', 'shortlog', '-sne'], true)
            ->willReturnOnConsecutiveCalls(null);

        $this->assertSame([], $this->repository->getContributors());
    }

    #[Test]
    public function it_returns_current_tag()
    {
        $this->repository->method('runProcess')
            ->with(['git', 'describe', '--tags', '--abbrev=0'], true)
            ->willReturnOnConsecutiveCalls('v1.2.3');

        $this->assertSame('v1.2.3', $this->repository->getCurrentTag());
    }

    #[Test]
    public function it_returns_null_on_missing_current_tag()
    {
        $this->repository->method('runProcess')
            ->with(['git', 'describe', '--tags', '--abbrev=0'], true)
            ->willReturnOnConsecutiveCalls(null);

        $this->assertNull($this->repository->getCurrentTag());
    }

    #[Test]
    public function it_returns_last_commit()
    {
        $gitOutput = '{abc123|John Doe|john@example.com|2025-09-25 12:00:00|Fix bug}';

        $this->repository->method('runProcess')
            ->with(['git', 'log', '-1', '--pretty={%H|%an|%ae|%ad|%s}'], true)
            ->willReturnOnConsecutiveCalls('abc123|John Doe|john@example.com|2025-09-25 12:00:00|Fix bug');

        $commit = $this->repository->getLastCommit();

        $this->assertInstanceOf(Commit::class, $commit);
        $this->assertSame('abc123', $commit->getHash());
        $this->assertSame('Fix bug', $commit->getMessage());
    }

    #[Test]
    public function it_returns_null_on_missing_last_commit()
    {
        $this->repository->method('runProcess')
            ->with(['git', 'log', '-1', '--pretty={%H|%an|%ae|%ad|%s}'], true)
            ->willReturnOnConsecutiveCalls(null);

        $this->assertNull($this->repository->getLastCommit());
    }

    #[Test]
    public function it_returns_last_commits_with_limit()
    {
        $output = implode("\n", [
            'abc123|John Doe|john@example.com|2025-09-25 12:00:00|Fix bug',
            'def456|Jane Smith|jane@example.com|2025-09-24 11:00:00|Add feature',
        ]);

        $this->repository->method('runProcess')
            ->with(['git', 'log', '-n2', '--date=iso', '--pretty={%H|%an|%ae|%ad|%s}'], true)
            ->willReturnOnConsecutiveCalls($output);

        $commits = $this->repository->getLastCommits(2);

        $this->assertCount(2, $commits);
        $this->assertInstanceOf(Commit::class, $commits[0]);
        $this->assertSame('abc123', $commits[0]->getHash());
        $this->assertSame('def456', $commits[1]->getHash());
    }

    #[Test]
    public function it_returns_last_commits_with_limit_with_incomplete_line()
    {
        $badOutput = "abc123|John Doe|john@example.com|2025-09-25 12:00:00|Fix bug\nbadline";
        $this->repository->method('runProcess')
            ->with(['git', 'log', '-n2', '--date=iso', '--pretty={%H|%an|%ae|%ad|%s}'], true)
            ->willReturnOnConsecutiveCalls($badOutput);

        $commits = $this->repository->getLastCommits(2);
        $this->assertCount(1, $commits);
    }

    #[Test]
    public function it_returns_last_commit_hash_or_null()
    {
        $this->repository->method('runProcess')
            ->with(['git', 'rev-parse', 'HEAD'], true)
            ->willReturnOnConsecutiveCalls('abc123');

        $this->assertSame('abc123', $this->repository->getLastCommitHash());
    }

    #[Test]
    public function it_returns_null_on_missing_last_commit_hash()
    {
        $this->repository->method('runProcess')
            ->with(['git', 'rev-parse', 'HEAD'], true)
            ->willReturnOnConsecutiveCalls(null);

        $this->assertNull($this->repository->getLastCommitHash());
    }

    #[Test]
    public function it_returns_name_and_owner_based_on_remote_url()
    {
        $this->repository->method('runProcess')
            ->with(['git', 'config', '--get', 'remote.origin.url'], true)
            ->willReturnOnConsecutiveCalls(
                'git@github.com:owner/repo.git',
                'git@github.com:owner/repo.git'
            );

        $this->assertSame('repo', $this->repository->getName());
        $this->assertSame('owner', $this->repository->getOwner());
    }

    #[Test]
    public function it_returns_null_based_on_invalid_remote_url()
    {
        $this->repository->method('runProcess')
            ->with(['git', 'config', '--get', 'remote.origin.url'], true)
            ->willReturnOnConsecutiveCalls(null, null);

        $this->assertNull($this->repository->getName());
        $this->assertNull($this->repository->getOwner());
    }

    #[Test]
    public function it_returns_array_of_tags()
    {
        $output = implode("\n", [
            'v1.0.0|2025-09-20T10:00:00+00:00',
            'v1.1.0|2025-09-22T15:00:00+00:00',
        ]);

        $this->repository->method('runProcess')
            ->with(['git', 'for-each-ref', '--sort=creatordate', '--format', '%(refname:strip=2)|%(creatordate:iso)', 'refs/tags'], true)
            ->willReturnOnConsecutiveCalls($output);

        $tags = $this->repository->getTags();

        $this->assertCount(2, $tags);
        $this->assertInstanceOf(Tag::class, $tags[0]);
        $this->assertSame('v1.0.0', $tags[0]->getName());
        $this->assertSame('v1.1.0', $tags[1]->getName());
    }

    #[Test]
    public function it_returns_empty_array_of_tags()
    {
        $this->repository->method('runProcess')
            ->with(['git', 'for-each-ref', '--sort=creatordate', '--format', '%(refname:strip=2)|%(creatordate:iso)', 'refs/tags'], true)
            ->willReturnOnConsecutiveCalls(null);

        $this->assertSame([], $this->repository->getTags());
    }

    #[Test]
    public function it_guesses_default_branch()
    {
        $this->repository = $this->getMockBuilder(Repository::class)
            ->onlyMethods(['getRemoteUrl'])
            ->getMock();

        $this->repository->method('getRemoteUrl')->willReturnOnConsecutiveCalls(null);

        $this->assertSame('master', $this->repository->guessDefaultBranch());
    }

    #[Test]
    public function it_checks_true_for_uncommitted_changes()
    {
        $this->repository->method('runProcess')
            ->with(['git', 'status', '--porcelain'], true)
            ->willReturnOnConsecutiveCalls(' M src/Foo.php');

        $this->assertTrue($this->repository->hasUncommittedChanges());
    }

    #[Test]
    public function it_checks_false_for_uncommitted_changes()
    {
        $this->repository->method('runProcess')
            ->with(['git', 'status', '--porcelain'], true)
            ->willReturnOnConsecutiveCalls(null);

        $this->assertFalse($this->repository->hasUncommittedChanges());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getMockBuilder(Repository::class)
            ->onlyMethods(['runProcess'])
            ->getMock();
    }
}
