<?php

namespace App\Console\Commands;

use App\Models\Ad;
use Illuminate\Console\Command;

class UpdatePromotedAds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:update-promoted';

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
        $now = now();

        Ad::where('approved', 1)
            ->where('promotion_expiry', '<', $now)
            ->update(['approved' => 0]);

        $this->info('Approved ads with expired promotions updated successfully.');
    }
}
