<?php

declare(strict_types=1);

/**
 * ©️ copyright 2025 - johninamillion
 * 🙏🏻 Righteousness keepeth him that is upright in the way: but wickedness overthroweth the sinner. - Proverbs 13:6, KJV.
 */

namespace johninamillion\Git\Traits;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Interact with Git CLI.
 *
 * @package johninamillion/php-github
 * @since   0.1.0
 */
trait InteractsWithGitCli
{
    /**
     * Get config.
     *
     * @param  string      $key
     * @return string|null
     */
    protected function getConfig(string $key): ?string
    {

        return $this->runProcess(['git', 'config', $key]);
    }

    /**
     * Run the process.
     *
     * @param  array<string> $command
     * @param  bool          $allowFailure
     * @return string|null
     */
    protected function runProcess(array $command, bool $allowFailure = false): ?string
    {
        $process = new Process($command);
        $success = $process->run() === 0;

        if (!$success && !$allowFailure) {
            throw new ProcessFailedException($process);
        }

        return $success
            ? trim($process->getOutput())
            : null;
    }
}
