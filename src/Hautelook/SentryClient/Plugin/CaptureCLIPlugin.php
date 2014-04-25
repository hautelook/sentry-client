<?php

namespace Hautelook\SentryClient\Plugin;

use Guzzle\Common\Event;
use Hautelook\SentryClient\Command\CaptureCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class CaptureCLIPlugin implements EventSubscriberInterface
{
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

        if (!isset($_SERVER['argv'])) {
            return;
        }

        $extra = $command['extra'];
        $extra['argv'] = $_SERVER['argv'];
        $command['extra'] = $extra;
    }
}
