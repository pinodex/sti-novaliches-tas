<?php

namespace App\Console\Commands\Lab;

use Mail;
use Illuminate\Console\Command;
use App\Lab\Mail\TestMessage;

class QueuedEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lab:queued-email {address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Queued Email Sending';

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
        $emailAddress = $this->argument('address');

        $this->info("Queuing test email to {$emailAddress}...");

        Mail::to($emailAddress)->queue(new TestMessage);

        $this->info('Test email added to queue');
    }
}
