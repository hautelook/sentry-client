<?php

namespace Hautelook\SentryClient\Tests\Command;

use Hautelook\SentryClient\Client;

class CaptureCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testMissingRequiredParameters()
    {
        $this->setExpectedException('Guzzle\Service\Exception\ValidationException');

        $client = $this->createClient();
        $client->getCommand('capture')->prepare();
    }

    public function testValidParameters()
    {
        $client = $this->createClient();

        $client
            ->getCommand('capture', array(
                'message' => 'Foo bar',
            ))
            ->prepare()
        ;

        $client
            ->getCommand('capture', array(
                'message' => 'Foo bar',
                'tags' => array(
                    'env' => 'dev',
                ),
                'modules' => array(
                    'symfony/symfony' => '2.4.0-dev',
                ),
            ))
            ->prepare()
        ;
    }

    private function createClient()
    {
        return Client::create(array(
            'public_key' => 'public',
            'secret_key' => 'secret',
            'project_id' => '1337',
        ));
    }
}
