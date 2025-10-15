<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        echo "Laravel timezone: " . config('app.timezone') . "\n";
        echo "Current time: " . now() . "\n"; // usa Carbon
    }
}
