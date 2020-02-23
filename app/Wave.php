<?php

namespace App;

class Wave
{
    const TOTAL_PULSES = 111;

    const CLAIRES_WAND = '16b93840';

    const ANNS_WAND = '1c160cc0';

    const KNOWN_WANDS = [self::CLAIRES_WAND, self::ANNS_WAND];

    public $pulses = [];

    public $padding;

    public $wandId;

    public $magnitude;

    public $createdMicrotime;

    public function __construct(array $pulses)
    {
        $this->createdMicrotime = microtime(true);
        $this->pulses = $pulses;
        $raw = $this->getRawData();
        $this->padding = substr($raw, 0, 8);
        // Throwing away the last two bits of the id field since they are 
        // not consistent.
        $this->wandId = base_convert(substr($raw, 8, 30), 2, 16);
        $this->magnitude = base_convert(substr($raw, 40, 16), 2, 10);

        if (!in_array($this->wandId, self::KNOWN_WANDS)) {
            throw new \Exception("Unknown wand: {$this->wandId}");
        }
    }

    public function __toString()
    {
        return "Name: " . $this->getWandName() . " ID: {$this->wandId} - Magnitude: {$this->magnitude}";
    }

    /**
     * Gets a human readable name for the wand
     * 
     * @return string
     */
    public function getWandName()
    {
        $names = [
            self::CLAIRES_WAND => "Claire's Wand",
            self::ANNS_WAND => "Ann's Wand"
        ];
        return $names[$this->wandId];
    }

    /**
     * Gets a character for rendering in the game.
     * 
     * @return string
     */
    public function getSymbol()
    {
        $symbols = [
            self::CLAIRES_WAND => "C",
            self::ANNS_WAND => "A"
        ];
        return $symbols[$this->wandId];
    }

    /**
     * Converts the pulse data into 56 bits.
     * 
     * @return string
     */
    public function getRawData()
    {
        $i = 0;
        $cycles = collect();
        while ($i < count($this->pulses)) {
            // Working around the missing last space
            $space = $i === 110 
                ? new IrPulse(IrPulse::TYPE_SPACE, Cycle::PULSE_MIN - $this->pulses[$i]->time)
                : $this->pulses[$i+1];
            $cycle = new Cycle($this->pulses[$i], $space);
            $cycles->push($cycle);
            $i += 2;
        }
        return $cycles->implode('bit');
    }

    /**
     * Creates a wave from pulse data.
     * 
     * @param array $pulses
     * @return Wave|null
     */
    public static function makeFromPulses(array $pulses)
    {
        if (count($pulses) === self::TOTAL_PULSES) {
            $wave = new self($pulses);
            return $wave;
        }
        
        return null;
    }

    /**
     * A wave of the wand can often result in the creation of 
     * multiple wand objects. This function takes a buffered
     * set of waves and returns one significant wave for each 
     * wand in the set. Significance is determined by the 
     * wave's magnitude.
     * 
     * @return array The most significant wave for each wand
     */
    public static function findSignificantWaves(array $waves)
    {
        $bestWaves = [];
        foreach ($waves as $wave) {
            $bestWaves[$wave->wandId] = isset($bestWaves[$wave->wandId])
                ? self::mostSignificant($wave, $bestWaves[$wave->wandId])
                : $wave;
        }
        return $bestWaves;
    }

    /**
     * Compares two waves and determines which is more significant.
     * 
     * @param Wave $a
     * @param Wave $b
     * @return Wave
     */
    public static function mostSignificant(Wave $a, Wave $b)
    {
        return $a->magnitude > $b->magnitude ? $a : $b;
    }
}
