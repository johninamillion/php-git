<?php

declare(strict_types=1);

/**
 * ©️ copyright 2025 - johninamillion
 * 🙏🏻 For he testifieth, Thou art a priest for ever after the order of Melchisedec. - Hebrews 7:17, KJV.
 */

namespace johninamillion\Git;

use DateTimeImmutable;
use Exception;

/**
 * Commit.
 *
 * This class is used to get information about a commit.
 * It can be used to get the hash, author, date, and message of the commit.
 *
 * @package johninamillion/php-github
 * @since   0.1.0
 */
class Commit
{
    public function __construct(
        protected User $author,
        protected string $date,
        protected string $hash,
        protected string $message,
    ) {}

    /**
     * Returns the hash of the commit.
     *
     * @return string
     */
    public function getHash(): string
    {

        return $this->hash;
    }

    /**
     * Returns the author of the commit.
     *
     * @return User
     */
    public function getAuthor(): User
    {

        return $this->author;
    }

    /**
     * Returns the message of the commit.
     *
     * @return string
     */
    public function getMessage(): string
    {

        return $this->message;
    }

    /**
     * Returns the date of the commit.
     *
     * @return DateTimeImmutable
     * @throws Exception
     */
    public function getDate(): DateTimeImmutable
    {

        return new DateTimeImmutable($this->date);
    }

    /**
     * Returns whether the commit is a merge commit.
     *
     * @return bool
     */
    public function isMergeCommit(): bool
    {

        return str_starts_with($this->message, 'Merge');
    }

    /**
     * Returns the short hash of the commit.
     *
     * @return string
     */
    public function shortHash(): string
    {

        return substr($this->hash, 0, 7);
    }
}
