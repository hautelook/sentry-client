<?php

namespace Hautelook\SentryClient\Request\Factory;

use Hautelook\SentryClient\Request\Interfaces\Http;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class SymfonyHttpFactory implements HttpFactoryInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @param Request $request
     */
    public function setRequestStack(RequestStack $requestStack = null)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $request = $this->request;

        if (null === $request && null !== $this->requestStack) {
            $request = $this->requestStack->getCurrentRequest();
        }

        if (null === $request) {
            return null;
        }

        $http = new Http(
            $request->getUriForPath($request->getPathInfo()),
            $request->getMethod()
        );

        $queryString = $request->getQueryString();
        if (strlen($queryString) > 0) {
            $http->setQueryString($queryString);
        }
        $http->setData($request->request->all());
        $http->setCookies($request->cookies->all());
        $http->setHeaders(array_map(function (array $values) {
            return count($values) === 1 ? reset($values) : $values;
        }, $request->headers->all()));
        $http->setEnv($request->server->all());

        return $http;
    }
}
