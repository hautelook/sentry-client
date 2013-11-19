<?php

namespace Hautelook\SentryClient\Request\Factory;

use Hautelook\SentryClient\Request\Interfaces\Exception;
use Hautelook\SentryClient\Request\Interfaces\Frame;
use Hautelook\SentryClient\Request\Interfaces\SingleException;
use Hautelook\SentryClient\Request\Interfaces\StackTrace;
use Symfony\Component\Debug\Exception\FlattenException;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class ExceptionFactory
{
    private $fileBasePath;

    public function __construct($fileBasePath = null)
    {
        $this->fileBasePath = $fileBasePath;
    }

    /**
     * @param  \Exception $e
     * @return Exception
     */
    public function create(\Exception $e)
    {
        $singleExceptions = array();

        while ($e !== null) {
            $singleException = new SingleException($e->getMessage());
            $singleException->setType(get_class($e));
            $singleException->setModule(sprintf('%s:%s', $e->getFile(), $e->getLine()));
            $singleException->setStackTrace($this->createStackTrace($e));
            $singleExceptions[] = $singleException;

            $e = $e->getPrevious();
        }

        return new Exception(array_reverse($singleExceptions));
    }

    /**
     * @param  \Exception $e
     * @return StackTrace
     */
    public function createStackTrace(\Exception $e)
    {
        $flattenException = FlattenException::create($e);

        $frames = array();
        foreach ($flattenException->getTrace() as $entry) {
            $filename = $entry['file'];

            if (null !== $this->fileBasePath) {
                if (stripos($filename, $this->fileBasePath)) {
                    $filename = null;
                } else {
                    $filename = substr($filename, strlen($this->fileBasePath));
                }
            }

            $frames[] = $frame = new Frame(
                $filename,
                (strlen($entry['class']) > 0 ? $entry['class'] . '::' : ''). $entry['function'],
                $entry['class']
            );
            $frame->setLineNumber($entry['line']);
            $frame->setVars($this->getFrameVars($entry));

            $this->handleFrameContext($frame, $entry);
        }

        // When the error handler creates an error exception, the error handler will appear in the stack trace
        if ($e instanceof \ErrorException) {
            $frames = $this->filterFramesAfterErrorHandler($frames);
        }

        return new StackTrace(array_reverse($frames));
    }

    private function getFrameVars($entry)
    {
        $vars = array();
        foreach ($entry['args'] as $arg) {
            $vars[] = is_array($arg[1]) ? $arg[0] : $arg[1]; // get the arg value
        }

        return $vars;
    }

    private function handleFrameContext(Frame $frame, $entry)
    {
        if ($entry['line'] < 1) {
            return;
        }

        // TODO maybe cache this stuff, reading file on a production environment if bad
        if (is_readable($entry['file'])) {
            $fileContent = file_get_contents($entry['file']);
            $lines = explode("\n", $fileContent);
            $lineCount = count($lines);
            $lineIndex = $frame->getLineNumber() - 1;

            if ($lineCount < 1) {
                return;
            }

            // TODO not sure this is well handled
            $frame->setContextLine($lines[$lineIndex]);
            $frame->setPreContext(array_slice(
                $lines,
                max(0, $lineIndex - 5),
                min(5, $lineIndex)
            ));
            $frame->setPostContext(array_slice(
                $lines,
                min($lineCount, $lineIndex + 1),
                min(5, $lineCount - $lineIndex)
            ));
        }
    }

    private function filterFramesAfterErrorHandler(array $frames)
    {
        $errorHandlerFrameIndex = null;
        foreach ($frames as $index => $frame) {
            if ($frame->getFunction() === 'Hautelook\SentryClient\ErrorHandler::handleError') {
                $errorHandlerFrameIndex = $index;
                break;
            }
        }

        if (null !== $errorHandlerFrameIndex) {
            array_splice($frames, 0, $errorHandlerFrameIndex + 1);
        }

        return $frames;
    }
}
