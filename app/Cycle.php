<?php

namespace App;

class Cycle
{
    const PULSE_MAX = 1200;

    const PULSE_MIN = 1050;

    const HIGH_DIFF_MAX = 0.83;

    public $pulse;

    public $space;

    public $value;

    public $bit;

    /**
     * @param IrPulse $pulse
     * @param IrPulse $space
     * @return IrPulsePair
     */
    public function __construct(IrPulse $pulse, IrPulse $space)
    {
        if ($pulse->type !== IrPulse::TYPE_PULSE || $space->type !== IrPulse::TYPE_SPACE) {
            throw new Exception("Invalid cycle pulses - $pulse, $space");
        }

        $total = $pulse->time + $space->time;
        if ($total > self::PULSE_MAX || $total < self::PULSE_MIN) {
            throw new Exception("Invlaid cycle total - $pulse, $space");
        }

        $this->pulse = $pulse;
        $this->space = $space;
        $this->value = $this->getValue();
        $this->bit = $this->getValue() > self::HIGH_DIFF_MAX ? '1' : '0';
    }

    public function getValue()
    {
        return $this->pulse->time / $this->space->time;
    }
}
