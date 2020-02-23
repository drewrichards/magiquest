<?php

namespace App;

class IrProcessor
{
    const COMMAND = 'mode2 -d /dev/lirc0';

    const USLEEP_AFTER_INPUT = 1000;

    const STREAM_READ_BUFFER = 4096;

    private $irInputStream;

    private $pulses = [];

    public $failures = [];

    public function __construct()
    {
        $this->irInputStream = popen(self::COMMAND, "r");
        stream_set_blocking($this->irInputStream, false);
        stream_set_read_buffer($this->irInputStream, self::STREAM_READ_BUFFER);
    }

    /**
     * Creates wand waves from a stream of ir pulses.
     */
    public function processPulses()
    {
        $waves = [];
        $totalReads = 0;
        do {
            $lastReadHadData = false;
            while($line = fgets($this->irInputStream)) {
                $lastReadHadData = true;
                $pulse = IrPulse::createFromLine($line);
                if (!$pulse) {
                    continue;
                }
    
                if ($pulse->isReset()) {
                    $wave = false;
                    try {
                        $wave = Wave::makeFromPulses($this->pulses);
                    } catch (\Exception $e) {
                        $this->failures[] = $e->getMessage();
                    }
                    
                    if ($wave) {
                        $waves[] = $wave;
                    }
                    $this->pulses = [];
                    continue;
                }
    
                $this->pulses[] = $pulse;
            }

            if ($lastReadHadData) {
                $totalReads++;
                usleep(self::USLEEP_AFTER_INPUT);
            }
            
        } while ($lastReadHadData);
        
        return $waves;
    }
}
