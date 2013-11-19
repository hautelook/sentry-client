<?php

namespace Hautelook\SentryClient\Plugin;

use Guzzle\Common\Event;
use Hautelook\SentryClient\Command\CaptureCommand;
use Hautelook\SentryClient\Request\Factory\HttpFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class CaptureHttpInterfacePlugin implements EventSubscriberInterface
{
    /**
     * @var HttpFactoryInterface
     */
    private $httpFactory;

    public function __construct(HttpFactoryInterface $httpFactory = null)
    {
        $this->httpFactory = $httpFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'command.before_prepare' => array('onCommandBeforePrepare', -1000),
        );
    }

    public function onCommandBeforePrepare(Event $event)
    {
        $command = $event['command'];

        if (!$command instanceof CaptureCommand) {
            return;
        }

        if (isset($command['sentry.interfaces.Http'])) {
            return;
        }

        $http = $this->httpFactory->create();

        if (null === $http) {
            return;
        }

        $command['sentry.interfaces.Http'] = $http;
    }
}
