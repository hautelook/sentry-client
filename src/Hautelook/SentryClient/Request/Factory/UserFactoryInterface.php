<?php

namespace Hautelook\SentryClient\Request\Factory;

use Hautelook\SentryClient\Request\Interfaces\User;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
interface UserFactoryInterface
{
    /**
     * @return User|null
     */
    public function create();
}
