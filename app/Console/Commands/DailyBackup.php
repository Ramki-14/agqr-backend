<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DailyBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a daily backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily backup...');
        $this->call('backup:run'); // This runs Spatie's backup
        $this->info('Backup completed successfully!');
    }
}
