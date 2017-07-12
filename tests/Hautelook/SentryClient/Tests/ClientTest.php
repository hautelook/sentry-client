<?php

namespace Hautelook\SentryClient\Tests;

use Hautelook\SentryClient\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateClientWithDsn()
    {
        $client = Client::create(array(
            'dsn' => 'https://public:secret@sentryapp.com/yolo/1337'
        ));

        $this->assertEquals('https://sentryapp.com/yolo/api/1337/', $client->getBaseUrl(true));
        $this->assertEquals('public', $client->getConfig('public_key'));
        $this->assertEquals('secret', $client->getConfig('secret_key'));
    }

    public function testCreateClientWithoutDsn()
    {
        $client = Client::create(array(
            'public_key' => 'public',
            'secret_key' => 'secret',
            'project_id' => '1337',
            'host' => 'sentryapp.com',
            'path' => '/yolo/',
        ));

        $this->assertEquals('https://sentryapp.com/yolo/api/1337/', $client->getBaseUrl(true));
        $this->assertEquals('public', $client->getConfig('public_key'));
        $this->assertEquals('secret', $client->getConfig('secret_key'));
    }

    public function testFailingCreateClient()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        Client::create(array());
    }

    public function testClientPort()
    {
        $client = Client::create(array(
            'public_key' => 'public',
            'secret_key' => 'secret',
            'project_id' => '1337',
            'port' => 6666,
        ));

        $this->assertContains(':6666', $client->getBaseUrl(true));
    }

    public function testRequestOptions()
    {
        $client = Client::create(array(
            'public_key' => 'public',
            'secret_key' => 'secret',
            'project_id' => '1337',
            Client::REQUEST_OPTIONS => array(
                'foo' => 'bar',
            ),
        ));

        $this->assertEquals('bar', $client->getDefaultOption('foo'));
    }

    public function testCaptureCommand()
    {
        $client = Client::create(array(
            'public_key' => 'public',
            'secret_key' => 'secret',
            'project_id' => '1337',
        ));

        $command = $client->getCommand('capture', array(
            'message' => 'foo',
        ));
        $request = $command->prepare();

        $this->assertEquals('/api/1337/store/', $request->getUrl(true)->getPath());
    }

    public function testCaptureCommandFilterData()
    {
        $client = Client::create(array(
            'public_key' => 'public',
            'secret_key' => 'secret',
            'project_id' => '1337',
        ));

        $command = $client->getCommand('capture', array(
            'message' => 'foo',
            'extra' => array(
                'password' => 'zomg password',
            ),
        ));
        $request = $command->prepare();

        $json = json_decode((string) $request->getBody(), true);

        $this->assertEquals('********', $json['extra']['password']);
    }

    public function testIgnoreExceptions()
    {
        $config = array(
            'host' => 'localhost:1',
            'public_key' => 'public',
            'secret_key' => 'secret',
            'project_id' => '1337',
            Client::CURL_OPTIONS => array(
                CURLOPT_CONNECTTIMEOUT => 0,
            ),

            'ignored_exceptions' => array(
                'InvalidArgumentException' => true,
                'RuntimeException' => false,
                'Exception',
            ),
        );

        /**
         * @var Client $client
         */
        $client = $this->getMockBuilder('Hautelook\SentryClient\Client')
            ->setConstructorArgs(array($config))
            ->setMethods(array('capture'))
            ->enableProxyingToOriginalMethods()
            ->getMockForAbstractClass();

        $client->expects($this->once())
            ->method('capture');

        $this->assertNull($client->captureException(new \Exception()));
        $this->assertNull($client->captureException(new \InvalidArgumentException()));

        $client->captureException(new \RuntimeException());
    }
}
