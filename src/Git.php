<?php

declare(strict_types=1);

/**
 * ©️ copyright 2025 - johninamillion
 * 🙏🏻 For he hath put all things under his feet. But when he saith all things are put under him, it is manifest that he is excepted, which did put all things under him. - I Corinthians 15:27, KJV.
 */

namespace johninamillion\Git;

/**
 * Git.
 *
 * This class is used to get information about the repository and the current user.
 *
 * @package johninamillion/php-github
 * @since   0.1.0
 */
class Git
{
    /**
     * The repository.
     *
     * @var Repository
     */
    protected Repository $repository;

    /**
     * The user.
     *
     * @var User
     */
    protected User $user;

    /**
     * Returns the user.
     *
     * @return User
     */
    public function getCurrentUser(): User
    {

        return $this->user ??= new User();
    }

    /**
     * Returns the repository.
     *
     * @return Repository
     */
    public function getRepository(): Repository
    {

        return $this->repository ??= new Repository();
    }
}
