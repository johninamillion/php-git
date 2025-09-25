<?php

declare(strict_types=1);

/**
 * ©️ copyright 2025 - johninamillion
 * 🙏🏻 This thou knowest, that all they which are in Asia be turned away from me; of whom are Phygellus and Hermogenes. - II Timothy 1:15, KJV.
 */

namespace johninamillion\Git\Traits;

use Exception;

/**
 * Interact with JSON.
 *
 * @package johninamillion/php-github
 * @since   0.1.0
 */
trait InteractsWithJson
{
    /**
     * JSON depth.
     *
     * @static
     * @var int<1,max>
     */
    public static int $jsonDepth = 512;

    /**
     * Parse JSON response.
     *
     * @param  string             $response
     * @param  bool               $allowFailure
     * @return array<mixed>|false
     */
    protected function parseJson(string $response, bool $allowFailure = true): array|false
    {
        try {

            return (array)json_decode($response, true, static::$jsonDepth, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {

            return $allowFailure ? [] : false;
        }
    }
}
