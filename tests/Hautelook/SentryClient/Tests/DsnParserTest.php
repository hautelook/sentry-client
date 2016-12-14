<?php

namespace Hautelook\SentryClient\Tests;

use Hautelook\SentryClient\DsnParser;

class DsnParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestParseData
     */
    public function testParse($dsn, $expectedResult)
    {
        $this->assertEquals($expectedResult, DsnParser::parse($dsn));
    }

    public function getTestParseData()
    {
        return array(
            array(
                'https://public:secret@example.com/sentry/project-id',
                array(
                    'protocol' => 'https',
                    'public_key' => 'public',
                    'secret_key' => 'secret',
                    'host' => 'example.com',
                    'port' => null,
                    'path' => '/sentry/',
                    'project_id' => 'project-id',
                )
            ),
            array(
                'https://public:secret@example.com/sentry/project-id/',
                array(
                    'protocol' => 'https',
                    'public_key' => 'public',
                    'secret_key' => 'secret',
                    'host' => 'example.com',
                    'port' => null,
                    'path' => '/sentry/',
                    'project_id' => 'project-id',
                )
            ),
            array(
                'https://public:secret@example.com:33/sentry/project-id',
                array(
                    'protocol' => 'https',
                    'public_key' => 'public',
                    'secret_key' => 'secret',
                    'host' => 'example.com',
                    'port' => 33,
                    'path' => '/sentry/',
                    'project_id' => 'project-id',
                )
            ),
            array(
                'https://public:secret@example.com:33/project-id',
                array(
                    'protocol' => 'https',
                    'public_key' => 'public',
                    'secret_key' => 'secret',
                    'host' => 'example.com',
                    'port' => 33,
                    'path' => '/',
                    'project_id' => 'project-id',
                )
            ),
        );
    }

    /**
     * @dataProvider getTestParseInvalidDsnData
     */
    public function testParseInvalidDsn($dsn, $expectedException, $expectedExceptionMessage)
    {
        $this->setExpectedException($expectedException, $expectedExceptionMessage);

        DsnParser::parse($dsn);
    }

    public function getTestParseInvalidDsnData()
    {
        return array(
            array('', 'InvalidArgumentException', 'The DSN is missing the scheme, user, pass, host part(s).'),
            array('https://example.com/sentry/project-id', 'InvalidArgumentException', 'The DSN is missing the user, pass part(s).'),
            array('https://', 'InvalidArgumentException', 'Malformed DSN "https://".'),
            array('https://public:secret@example.com/sentry/project-id///', 'InvalidArgumentException', 'Invalid DSN path "/sentry/project-id///".'),
        );
    }
}
