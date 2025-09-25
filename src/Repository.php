<?php

declare(strict_types=1);

/**
 * ©️ copyright 2025 - johninamillion
 * 🙏🏻 Also he made before the house two pillars of thirty and five cubits high, and the chapiter that was on the top of each of them was five cubits. - II Chronicles 3:15, KJV.
 */

namespace johninamillion\Git;

use johninamillion\Git\Traits\InteractsWithGitCli;
use johninamillion\Git\Traits\InteractsWithJson;

/**
 * Repository.
 *
 * This class is used to get information about a repository.
 * It can be used to get the name, owner, remote URL, default branch, tags, contributors, and more.
 *
 * @package johninamillion/php-github
 * @since   0.1.0
 */
class Repository
{
    use InteractsWithGitCli;
    use InteractsWithJson;

    /**
     * Fallback branch.
     *
     * @static
     * @var string
     */
    public static string $fallbackBranch = 'master';

    /**
     * Returns the current branch.
     *
     * @return string
     */
    public function getBranch(): string
    {
        return $this->runProcess(['git', 'rev-parse', '--abbrev-ref', 'HEAD']) ?? static::$fallbackBranch;
    }

    /**
     * Returns the changed files.
     *
     * @return string[]
     */
    public function getChangedFiles(): array
    {
        $output = $this->runProcess(['git', 'status', '--porcelain'], true);

        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $files = [];

        foreach ($lines as $line) {
            // Match the filename after the status (2 characters + space or tab)
            if (preg_match('/^[ MADRCU?!]{1,2}\s+(.*)$/', $line, $matches)) {
                $files[] = $matches[1];
            }
        }

        return $files;
    }

    /**
     * Returns the contributors.
     *
     * @return User[]
     */
    public function getContributors(): array
    {
        $output = $this->runProcess(['git', 'shortlog', '-sne'], true);

        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $contributors = [];

        foreach ($lines as $line) {
            // line example: "  14\tJohn Doe <john@example.com>"
            if (preg_match('/^\s*(\d+)\s+(.*?)\s+<([^>]+)>$/', $line, $matches)) {
                $contributors[] = new User(
                    $matches[2],
                    $matches[3]
                );
            }
        }

        return $contributors;
    }

    /**
     * Returns the current tag.
     *
     * @return string|null
     */
    public function getCurrentTag(): ?string
    {

        return $this->runProcess(['git', 'describe', '--tags', '--abbrev=0'], true);
    }

    /**
     * Returns the last commit.
     *
     * @return Commit|null
     */
    public function getLastCommit(): ?Commit
    {
        $output = $this->runProcess(['git', 'log', '-1', '--pretty={%H|%an|%ae|%ad|%s}'], true);

        if (!$output || !str_contains($output, '|')) {
            return null;
        }

        [$hash, $authorName, $authorEmail, $date, $message] = explode('|', $output);

        return new Commit(
            author: new User($authorName, $authorEmail),
            date: $date,
            hash: $hash,
            message: $message,
        );
    }

    /**
     * Returns the last commits.
     *
     * @param  int<1,max> $limit
     * @return Commit[]
     */
    public function getLastCommits(int $limit = 10): array
    {
        $output = $this->runProcess(['git', 'log', "-n{$limit}", '--date=iso', '--pretty={%H|%an|%ae|%ad|%s}'], true);

        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $commits = [];

        foreach ($lines as $line) {
            [$hash, $authorName, $authorEmail, $date, $message] = explode('|', $line) + [null, null, null, null, null];

            if (!$hash || !$authorName || !$authorEmail || !$date || !$message) {
                continue;
            }

            $commits[] = new Commit(
                author: new User($authorName, $authorEmail),
                date: $date,
                hash: $hash,
                message: $message,
            );
        }

        return $commits;
    }

    /**
     * Returns the last commit hash.
     *
     * @return string|null
     */
    public function getLastCommitHash(): ?string
    {

        return $this->runProcess(['git', 'rev-parse', 'HEAD'], true);
    }

    /**
     * Returns the name of the repository.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        $name = explode('/', $this->getRemoteUrl() ?? '')[1] ?? null;

        return $name !== '' ? $name : null;
    }

    /**
     * Returns the remote URL of the repository.
     *
     * @return string|null
     */
    public function getRemoteUrl(): ?string
    {
        $remoteUrl = $this->runProcess(['git', 'config', '--get', 'remote.origin.url'], true);

        return trim(str_replace(
            ['git@github.com:', 'https://github.com/', '.git'],
            '',
            $remoteUrl ?? ''
        ));
    }

    /**
     * Returns the owner of the repository.
     *
     * @return string|null
     */
    public function getOwner(): ?string
    {
        $owner = explode('/', $this->getRemoteUrl() ?? '')[0];

        return $owner !== '' ? $owner : null;
    }

    /**
     * Returns the tags of the repository.
     *
     * @return Tag[]
     */
    public function getTags(): array
    {
        $output = $this->runProcess(['git', 'for-each-ref', '--sort=creatordate', '--format', '%(refname:strip=2)|%(creatordate:iso)', 'refs/tags'], true);

        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $tags = [];

        foreach ($lines as $line) {
            [$name, $date] = explode('|', $line) + [null, null];

            if (!$name || !$date) {
                continue;
            }

            $tags[] = new Tag(
                name: $name,
                date: $date,
            );
        }

        return $tags;
    }

    /**
     * Guess the default branch of the repository.
     *
     * @return string
     */
    public function guessDefaultBranch(): string
    {
        $remoteUrl = $this->getRemoteUrl();

        if (!$remoteUrl) {
            return static::$fallbackBranch;
        }

        $apiEndpoint = "https://api.github.com/repos/{$remoteUrl}";

        $curl = curl_init($apiEndpoint);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: github-user-fetcher/1.0',
            ],
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            return static::$fallbackBranch;
        }

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($statusCode === 200) {
            /** @var array<string,string> $data */
            $data = $this->parseJson((string)$response);

            return $data['default_branch'] ?? static::$fallbackBranch;
        }

        return static::$fallbackBranch;
    }

    /**
     * Check for uncommitted changes.
     *
     * @return bool
     */
    public function hasUncommittedChanges(): bool
    {
        $output = $this->runProcess(['git', 'status', '--porcelain'], true);

        return $output !== null;
    }
}
