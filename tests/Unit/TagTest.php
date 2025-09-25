<?php

namespace johninamillion\Git\Tests\Unit;

use johninamillion\Git\Tag;
use johninamillion\Git\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tag Test.
 *
 * @package johninamillion/php-github
 * @covers  Tag
 */
final class TagTest extends TestCase
{
    #[Test]
    public function it_initializes_name_and_date()
    {
        $tag = new Tag('v1.2.3', '2025-09-25 12:00:00');

        $this->assertSame('v1.2.3', $tag->getName());
        $this->assertSame('2025-09-25T12:00:00+00:00', $tag->getDate()->format('c'));
    }

    #[Test]
    public function it_returns_version_for_semver_tag()
    {
        $tag = new Tag('v1.2.3', '2025-09-25');

        $this->assertSame('1.2.3', $tag->getVersion());
    }

    #[Test]
    public function it_returns_null_version_for_non_semver_tag()
    {
        $tag = new Tag('release-1.2', '2025-09-25');

        $this->assertNull($tag->getVersion());
    }

    #[Test]
    public function it_detects_semantic_versioning()
    {
        $this->assertTrue((new Tag('v2.0.1', 'now'))->isSemanticVersioning());
        $this->assertTrue((new Tag('2.0.0', 'now'))->isSemanticVersioning());
        $this->assertFalse((new Tag('v2.0', 'now'))->isSemanticVersioning());
        $this->assertFalse((new Tag('release', 'now'))->isSemanticVersioning());
    }

    #[Test]
    public function it_can_compare_if_newer_than_other_tag()
    {
        $old = new Tag('v1.0.0', '2025-01-01');
        $new = new Tag('v2.0.0', '2025-09-25');

        $this->assertTrue($new->isNewerThan($old));
        $this->assertFalse($old->isNewerThan($new));
    }

    #[Test]
    public function it_can_compare_if_older_than_other_tag()
    {
        $older = new Tag('v1.0.0', '2025-01-01');
        $newer = new Tag('v1.1.0', '2025-06-01');

        $this->assertTrue($older->isOlderThan($newer));
        $this->assertFalse($newer->isOlderThan($older));
    }

    #[Test]
    public function it_can_compare_if_same_version_as_other_tag()
    {
        $tag1 = new Tag('v1.2.3', '2025-01-01');
        $tag2 = new Tag('1.2.3', '2025-06-01');

        $this->assertTrue($tag1->isSameAs($tag2));
    }

    #[Test]
    public function it_handles_invalid_versions_gracefully()
    {
        $tag1 = new Tag('alpha', '2025-01-01');
        $tag2 = new Tag('beta', '2025-06-01');

        $this->assertFalse($tag1->isNewerThan($tag2));
        $this->assertFalse($tag1->isOlderThan($tag2));
        $this->assertFalse($tag1->isSameAs($tag2));
    }
}
