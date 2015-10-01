<?php

namespace Hautelook\SentryClient\Request\Factory;

use Hautelook\SentryClient\Request\Interfaces\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class SymfonyUserFactory implements UserFactoryInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return null;
        }

        return new User($token->getUsername());
    }
}
