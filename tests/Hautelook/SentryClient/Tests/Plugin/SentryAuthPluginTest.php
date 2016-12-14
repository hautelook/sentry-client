<?php

namespace Hautelook\SentryClient\Tests\Plugin;

use Guzzle\Common\Event;
use Hautelook\SentryClient\Plugin\SentryAuthPlugin;

class SentryAuthPluginTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $plugin = new SentryAuthPlugin(
            'public',
            'secret',
            '4',
            'agent'
        );

        $expectedHeader = sprintf(
            'Sentry sentry_version=%s, sentry_client=%s, sentry_timestamp=%s, sentry_key=%s, sentry_secret=%s',
            '4',
            'agent',
            '7777777',
            'public',
            'secret'
        );

        $request = $this->createRequestMock();

        $request->expects($this->atLeastOnce())
            ->method('setHeader')
            ->with('X-Sentry-Auth', $expectedHeader);

        $plugin->onRequestBeforeSend(new Event(array(
            'request' => $request,
            'timestamp' => 7777777
        )));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Guzzle\Http\Message\Request
     */
    private function createRequestMock()
    {
        return $this->getMockBuilder('Guzzle\Http\Message\Request')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
