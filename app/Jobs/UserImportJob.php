<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\UserImportCompleted;
use App\Components\Import\UserImporter;
use App\Models\User;

class UserImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Components\Import\UserImporter
     */
    protected $importer;

    /**
     * @var \App\Models\User
     */
    protected $actor;

    /**
     * Create a new job instance.
     * 
     * @param \App\Components\Import\UserImporter $importer User importer instance
     *
     * @return void
     */
    public function __construct(UserImporter $importer, User $actor)
    {
        $this->importer = $importer;
        $this->actor = $actor;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->importer->import();

        $this->actor->notify(new UserImportCompleted);
    }
}
