<?php

namespace Hautelook\SentryClient\Command;

use Guzzle\Service\Command\DefaultRequestSerializer;
use Guzzle\Service\Command\LocationVisitor\VisitorFlyweight;
use Guzzle\Service\Command\OperationCommand;
use Guzzle\Service\Description\Operation;
use Hautelook\SentryClient\Client;
use Hautelook\SentryClient\Request\LocationVisitor\JsonVisitor;
use Ramsey\Uuid\Uuid;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class CaptureCommand extends OperationCommand
{
    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        if (!isset($this['event_id'])) {
            /**
             * Support the old namespace
             */
            if (class_exists('Rhumsaa\Uuid\Uuid')) {
                $uuid = \Rhumsaa\Uuid\Uuid::uuid4();
            } else {
                $uuid = Uuid::uuid4();
            }

            $this['event_id'] = str_replace('-', '', $uuid->toString());
        }

        if (!isset($this['timestamp'])) {
            $this['timestamp'] = new \DateTime();
        }
        if ($this['timestamp'] instanceof \DateTime) {
            $this['timestamp'] = clone $this['timestamp'];
            $this['timestamp']->setTimezone(new \DateTimeZone('UTC'));
            $this['timestamp'] = $this['timestamp']->format(\DateTime::ISO8601);
        }

        $factory = new VisitorFlyweight();
        $factory->addRequestVisitor('json', new JsonVisitor());
        $this->setRequestSerializer(new DefaultRequestSerializer($factory));
    }

    /**
     * {@inheritdoc}
     */
    protected function createOperation()
    {
        return new Operation(array(
            'name' => get_class($this),
            'httpMethod' => 'POST',
            'uri' => 'store/',

            'parameters' => array(
                // Required parameters

                'event_id' => array(
                    'required' => false,
                    'type' => 'string',
                    'location' => 'json',
                ),
                'message' => array(
                    'required' => true,
                    'type' => 'string',
                    'location' => 'json',
                ),
                'timestamp' => array(
                    'required' => false,
                    'type' => 'string',
                    'location' => 'json',
                ),
                'level' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array(
                        Client::LEVEL_DEBUG,
                        Client::LEVEL_INFO,
                        Client::LEVEL_WARNING,
                        Client::LEVEL_ERROR,
                        Client::LEVEL_FATAL,
                    ),
                    'default' => Client::LEVEL_ERROR,
                    'location' => 'json',
                ),
                'logger' => array(
                    'required' => true,
                    'type' => 'string',
                    'default' => 'root',
                    'location' => 'json',
                ),

                // Optional parameters

                'platform' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'php',
                    'location' => 'json',
                ),
                'culprit' => array(
                    'required' => false,
                    'type' => 'string',
                    'location' => 'json',
                ),
                'tags' => array(
                    'required' => false,
                    'type' => 'array',
                    'items' => array(
                        'type' => 'string',
                    ),
                    'location' => 'json',
                ),
                'server_name' => array(
                    'required' => false,
                    'type' => 'string',
                    'location' => 'json',
                ),
                'release' => array(
                    'required' => false,
                    'type' => 'string',
                    'location' => 'json',
                ),
                'modules' => array(
                    'required' => false,
                    'type' => 'array',
                    'items' => array(
                        'type' => 'string',
                    ),
                    'location' => 'json',
                ),
                'extra' => array(
                    'required' => false,
                    'type' => 'array',
                    'items' => array(
                        'type' => array('string', 'number', 'array'),
                    ),
                    'location' => 'json',
                ),

                // Interfaces

                'sentry.interfaces.Message' => array(
                    'required' => false,
                    'type' => 'object',
                    'instanceOf' => 'Hautelook\SentryClient\Request\Interfaces\Message',
                    'location' => 'json',
                ),
                'sentry.interfaces.Exception' => array(
                    'required' => false,
                    'type' => 'object',
                    'instanceOf' => 'Hautelook\SentryClient\Request\Interfaces\Exception',
                    'location' => 'json',
                ),
                'sentry.interfaces.StackTrace' => array(
                    'required' => false,
                    'type' => 'object',
                    'instanceOf' => 'Hautelook\SentryClient\Request\Interfaces\StackTrace',
                    'location' => 'json',
                ),
                'sentry.interfaces.Http' => array(
                    'required' => false,
                    'type' => 'object',
                    'instanceOf' => 'Hautelook\SentryClient\Request\Interfaces\Http',
                    'location' => 'json',
                ),
                'sentry.interfaces.Query' => array(
                    'required' => false,
                    'type' => 'object',
                    'instanceOf' => 'Hautelook\SentryClient\Request\Interfaces\Query',
                    'location' => 'json',
                ),
                'sentry.interfaces.User' => array(
                    'required' => false,
                    'type' => 'object',
                    'instanceOf' => 'Hautelook\SentryClient\Request\Interfaces\User',
                    'location' => 'json',
                ),
            ),
        ));
    }
}
