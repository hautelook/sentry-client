<?php

namespace Hautelook\SentryClient\Request\Interfaces;
use Guzzle\Common\ToArrayInterface;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class StackTrace implements ToArrayInterface
{
    /**
     * @var Frame[]
     */
    private $frames;

    public function __construct(array $frames)
    {
        foreach ($frames as $frame) {
            if (!$frame instanceof Frame && !$frame instanceof Template) {
                throw new \InvalidArgumentException(
                    'StackTrace frames should be instance of Hautelook\SentryClient\Request\Interfaces\Frames'
                );
            }
        }

        $this->frames = $frames;
    }

    /**
     * @return Frame[]
     */
    public function getFrames()
    {
        return $this->frames;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $frames = array_map(function (Frame $frame) {
            return $frame->toArray();
        }, $this->getFrames());

        if (count($frames) < 1) {
            $frames = new \ArrayObject(); // the sentry api would prefer an empty object rather than an empty array
        }

        return array(
            'frames' => $frames,
        );
    }
}
