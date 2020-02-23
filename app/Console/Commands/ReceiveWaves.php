<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\IrPulse;
use App\Wave;

class ReceiveWaves extends Command
{
    const DURATION_CUTOFF = 0.3481;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wave:receive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Receives wave data from the ir sensor';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->receive();
    }

    /**
     * Receives data from the is sensor.
     */
    public function receive()
    {
        $command = 'mode2 -d /dev/lirc0';
        $h = popen($command, "r");

        $pulses = [];

        while($line = fgets($h)) {
            $pulse = IrPulse::createFromLine($line);
            if (!$pulse) {
                continue;
            }

            //$this->line($pulse);
            if ($pulse->isReset()) {
                $wave = false;
                try {
                    $wave = Wave::makeFromPulses($pulses);
                } catch (\Exception $e) {
                    $this->warn($e->getMessage());
                }
                
                if ($wave) {
                    $this->line($wave);
                } else {
                    $this->warn("Wave not created. Pulses: " . count($pulses));
                }
                $pulses = [];
                continue;
            }

            $pulses[] = $pulse;
        }
    }
}
