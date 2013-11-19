<?php

namespace Hautelook\SentryClient\Request\Factory;

use Hautelook\SentryClient\Request\Interfaces\Http;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
interface HttpFactoryInterface
{
    /**
     * @return Http|null
     */
    public function create();
}
