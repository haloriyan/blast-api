<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunHourlyTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-hourly-task';

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
        $controller = new UserController();
        $controller->hourlyTasks();
    }
}
