<?php

namespace johninamillion\Git\Tests\Unit;

use johninamillion\Git\Tests\TestCase;
use johninamillion\Git\User;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

/**
 * User Test.
 *
 * @package johninamillion/php-github
 * @covers  User
 */
final class UserTest extends TestCase
{
    #[Test]
    public function it_accepts_name_email_and_username_in_constructor()
    {
        $user = new User('Jane Doe', 'jane@example.com', 'janedoe');

        $this->assertSame('Jane Doe', $this->getPrivateProperty($user, 'name'));
        $this->assertSame('jane@example.com', $this->getPrivateProperty($user, 'email'));
        $this->assertSame('janedoe', $this->getPrivateProperty($user, 'username'));
    }

    #[Test]
    public function it_can_initialize_user_with_git_config()
    {
        $mock = $this->getMockBuilder(User::class)
            ->onlyMethods(['runProcess', 'guessUsernameFromCommits', 'guessUsernameFromGitHubCLI', 'guessUsernameFromRemote'])
            ->getMock();

        $mock->method('runProcess')
            ->willReturnMap([
                [['git', 'config', 'user.name'], false, 'John Doe'],
                [['git', 'config', 'user.email'], false, 'john@example.com'],
            ]);

        $mock->method('guessUsernameFromCommits')->willReturn(null);
        $mock->method('guessUsernameFromGitHubCLI')->willReturn(null);
        $mock->method('guessUsernameFromRemote')->willReturn('johndoe');

        // trigger __construct manually
        $reflection = new ReflectionClass($mock);
        $constructor = $reflection->getConstructor();
        $constructor->invoke($mock);

        $this->assertSame('John Doe', $this->getPrivateProperty($mock, 'name'));
        $this->assertSame('john@example.com', $this->getPrivateProperty($mock, 'email'));
        $this->assertSame('johndoe', $this->getPrivateProperty($mock, 'username'));
    }

    #[Test]
    public function it_counts_commits_correctly()
    {
        $mock = $this->getMockBuilder(User::class)
            ->onlyMethods(['runProcess'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->setPrivateProperty($mock, 'email', 'john@example.com');

        $mock->method('runProcess')
            ->willReturn("commit1\ncommit2\ncommit3");

        $this->assertSame(3, $mock->getCommitCount());
    }

    #[Test]
    public function it_returns_zero_when_commit_count_fails()
    {
        $mock = $this->getMockBuilder(User::class)
            ->onlyMethods(['runProcess'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->setPrivateProperty($mock, 'email', 'john@example.com');

        $mock->method('runProcess')->willReturn(null);

        $this->assertSame(0, $mock->getCommitCount());
    }

    #[Test]
    public function guess_username_from_commits()
    {
        $mock = $this->getMockBuilder(User::class)
            ->onlyMethods(['runProcess'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->setPrivateProperty($mock, 'name', 'John Doe');

        $mock->method('runProcess')
            ->willReturn("John Doe:john.doe@users.noreply.github.com\nJane:other@users.noreply.github.com");

        $username = $this->invokeMethod($mock, 'guessUsernameFromCommits');

        $this->assertSame('john.doe', $username);
    }

    #[Test]
    public function guess_username_from_github_cli()
    {
        $mock = $this->getMockBuilder(User::class)
            ->onlyMethods(['runProcess'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('runProcess')
            ->willReturn("Logged in to github.com as johndoe.");

        $username = $this->invokeMethod($mock, 'guessUsernameFromGitHubCLI');

        $this->assertSame('johndoe', $username);
    }

    #[Test]
    public function guess_username_from_remote()
    {
        $mock = $this->getMockBuilder(User::class)
            ->onlyMethods(['runProcess'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('runProcess')
            ->willReturn('git@github.com:johndoe/my-repo.git');

        $username = $this->invokeMethod($mock, 'guessUsernameFromRemote');

        $this->assertSame('johndoe', $username);
    }
}
