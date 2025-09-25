<?php

declare(strict_types=1);

/**
 * ©️ copyright 2025 - johninamillion
 * 🙏🏻 That the triumphing of the wicked is short, and the joy of the hypocrite but for a moment? - Job 20:5, KJV.
 */

namespace johninamillion\Git;

use DateTimeImmutable;
use Exception;

/**
 * Tag.
 *
 * This class is used to get information about a tag.
 * It can be used to get the name, date, and version of the tag.
 *
 * @package johninamillion/php-github
 * @since   0.1.0
 */
class Tag
{
    /**
     * Constructor.
     *
     * @param string $name
     * @param string $date
     */
    public function __construct(
        protected string $name,
        protected string $date,
    ) {}

    /**
     * Returns the date of the tag.
     *
     * @return DateTimeImmutable|null
     * @throws Exception
     */
    public function getDate(): ?DateTimeImmutable
    {
        return new DateTimeImmutable($this->date);
    }

    /**
     * Returns the name of the tag.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Checks if the tag is newer than another tag.
     *
     * @param  Tag  $other
     * @return bool
     */
    public function isNewerThan(Tag $other): bool
    {
        $thisVersion = $this->getVersion();
        $otherVersion = $other->getVersion();

        return $thisVersion !== null && $otherVersion !== null
            ? version_compare($thisVersion, $otherVersion, '>')
            : false;
    }

    /**
     * Returns the version of the tag.
     *
     * @return string|null
     */
    public function getVersion(): ?string
    {

        return $this->isSemanticVersioning()
            ? ltrim($this->name, 'v')
            : null;
    }

    /**
     * Checks if the tag is a semantic version.
     *
     * @return bool
     */
    public function isSemanticVersioning(): bool
    {

        return preg_match('/^v?\d+\.\d+\.\d+$/', $this->name) === 1;
    }

    /**
     * Checks if the tag is older than another tag.
     *
     * @param  Tag  $other
     * @return bool
     */
    public function isOlderThan(Tag $other): bool
    {
        $thisVersion = $this->getVersion();
        $otherVersion = $other->getVersion();

        return $thisVersion !== null && $otherVersion !== null
            ? version_compare($thisVersion, $otherVersion, '<')
            : false;
    }

    /**
     * Checks if the tag is the same as another tag.
     *
     * @param  Tag  $other
     * @return bool
     */
    public function isSameAs(Tag $other): bool
    {
        $thisVersion = $this->getVersion();
        $otherVersion = $other->getVersion();

        return $thisVersion !== null && $otherVersion !== null && $thisVersion === $otherVersion;
    }
}
