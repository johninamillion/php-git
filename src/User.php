<?php

declare(strict_types=1);

/**
 * ©️ copyright 2025 - johninamillion
 * 🙏🏻 I am become a stranger unto my brethren, and an alien unto my mother’s children. - Psalms 69:8, KJV.
 */

namespace johninamillion\Git;

use johninamillion\Git\Traits\InteractsWithGitCli;

/**
 * User.
 *
 * This class is used to get information about the user.
 * It can be used to get the name, email, and username of the user.
 *
 * @package johninamillion/php-github
 * @since   0.1.0
 */
class User
{
    use InteractsWithGitCli;

    protected ?string $email;
    protected ?string $name;
    protected ?string $username;

    /**
     * Constructor.
     *
     * @param string|null $name
     * @param string|null $email
     * @param string|null $username
     */
    public function __construct(
        ?string $name = null,
        ?string $email = null,
        ?string $username = null,
    ) {
        $this->name = $name ?? $this->runProcess(['git', 'config', 'user.name']);
        $this->email = $email ?? $this->runProcess(['git', 'config', 'user.email']);
        $this->username = $username ?? $this->guessGitHubUsername();
    }

    /**
     * Get the number of commits made by the user.
     *
     * @return int
     */
    public function getCommitCount(): int
    {
        $output = $this->runProcess([
            'git', 'log', '--author=' . $this->email, '--pretty=oneline',
        ], true);

        return $output ? count(explode("\n", trim($output))) : 0;
    }

    /**
     * Get the email of the user.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Get the name of the user.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the username of the user.
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Guess the Git username.
     *
     * @return string|null
     */
    protected function guessGitHubUsername(): ?string
    {

        return $this->guessUsernameFromCommits()
            ?? $this->guessUsernameFromGitHubCLI()
            ?? $this->guessUsernameFromRemote()
            ?? null;
    }

    /**
     * Guess the username from the commits.
     *
     * @return string|null
     */
    protected function guessUsernameFromCommits(): ?string
    {
        $authorName = strtolower($this->name ?? '');
        $output = $this->runProcess([
            'git', 'log', '--author=@users.noreply.github.com', '--pretty=%an:%ae', '--reverse',
        ], true);

        if ($output !== null) {
            foreach (explode("\n", $output) as $line) {
                if (!$line) {
                    continue;
                }

                [$name, $email] = explode(':', trim($line)) + [null, null];
                if ($name && $email && strtolower($name) === $authorName && !str_contains($name, '[bot]')) {

                    return explode('@', $email)[0];
                }
            }
        }

        return null;
    }

    /**
     * Guess the username from the Git CLI.
     *
     * @return string|null
     */
    protected function guessUsernameFromGitHubCLI(): ?string
    {
        $output = $this->runProcess(['gh', 'auth', 'status', '-h', 'github.com'], true);

        if ($output !== null && preg_match('/Logged in to github\.com as ([a-zA-Z0-9-_]+)/', $output, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Guess the username from the remote URL.
     *
     * @return string|null
     */
    protected function guessUsernameFromRemote(): ?string
    {
        if ($remoteUrl = $this->runProcess(['git', 'config', 'remote.origin.url'], true)) {
            $remoteUrl = trim(str_replace(['git@github.com:', 'https://github.com/', '.git'], '', $remoteUrl));

            return explode('/', $remoteUrl)[0];
        }

        return null;
    }
}
