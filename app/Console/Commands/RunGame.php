<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Game;
use App\Renderers\ConsoleRenderer;
use App\Renderers\BlinkstickRenderer;

class RunGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $game = new Game([
            //new ConsoleRenderer(),
            new BlinkstickRenderer(),
        ]);
        $game->loop();
    }

    public function process()
    {
        $command = 'mode2 -d /dev/lirc0';
        $h = popen($command, "r");
        stream_set_blocking($h, false);
        stream_set_read_buffer($h, 0);

        while (true) {
            $this->line("Starting");
            $contents = '';
            while ($part = fread($h, 8192)) {
                $contents .= $part;
                echo $part . "\n";
            }
            echo $contents . "\n";
            $this->warn('sleeping');
            sleep(3);
        }
    }
}
