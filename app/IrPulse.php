<?php

namespace App;

class IrPulse
{
    const TYPE_PULSE = 'pulse';

    const TYPE_SPACE = 'space';

    const TYPE_TIMEOUT = 'timeout';

    const VALID_TYPES = [self::TYPE_PULSE, self::TYPE_SPACE, self::TYPE_TIMEOUT];

    const RESET_TIME = 10000;

    public $type;

    public $time;
    
    /**
     * @param string $type
     * @param int $time
     */
    public function __construct($type, $time)
    {
        $this->type = $type;
        $this->time = $time;
    }

    public function __toString()
    {
        return "Type: {$this->type}, Time: {$this->time}, Reset: " . $this->isReset();
    }

    /**
     * Determines if the pulse if the start/end of a wave.
     * 
     * @return Boolean
     */
    public function isReset()
    {
        return $this->type === self::TYPE_TIMEOUT
            || ($this->type === self::TYPE_SPACE && $this->time > self::RESET_TIME);
    }

    /**
     * Creates a pulse from a log line
     * 
     * @param string $line
     * @return IrPulse|null
     */
    public static function createFromLine($line)
    {
        preg_match('/^(.*?)\s+(.*)$/', trim($line), $parts);
        if (in_array($parts[1], self::VALID_TYPES)) {
            return new self($parts[1], (int) $parts[2]);
        }
    }
}
