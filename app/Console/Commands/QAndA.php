<?php

namespace App\Console\Commands;

use App\Services\QAndAService;
use Illuminate\Console\Command;

class QAndA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qanda:interactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs an interactive command line based Q And A system.';


    /**
     * The service which handle tje business logic.
     *
     * @var string
     */
    protected $service;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(QAndAService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Create your interactive Q And A system here. Be sure to make use of all of Laravels functionalities.
        return $this->service->excute($this);
    }
}
