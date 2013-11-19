<?php

namespace Hautelook\SentryClient\Plugin;

use Guzzle\Common\Event;
use Hautelook\SentryClient\Command\CaptureCommand;
use Hautelook\SentryClient\Request\Factory\UserFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class CaptureUserInterfacePlugin implements EventSubscriberInterface
{
    /**
     * @var UserFactoryInterface
     */
    private $userFactory;

    public function __construct(UserFactoryInterface $userFactory = null)
    {
        $this->userFactory = $userFactory;
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

        if (isset($command['sentry.interfaces.User'])) {
            return;
        }

        $user = $this->userFactory->create();

        if (null === $user) {
            return;
        }

        $command['sentry.interfaces.User'] = $user;
    }
}
