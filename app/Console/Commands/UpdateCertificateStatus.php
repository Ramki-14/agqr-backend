<?php

namespace App\Console\Commands;

use App\Models\AssociateClientCertificate;
use App\Models\Certificate;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdateCertificateStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:certificate-status';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update certificate and order statuses based on next_surveillance date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today(); // Keep the original date
      $sixtyDaysAgo = $today->copy()->subDays(60); // 60 days before today
         $yesterday = $today->copy()->subDays(1); // Yesterday
         $fortyFiveDaysLater = $today->copy()->addDays(45); // 45 days from today
    
        // Update Certificates
    
        // Update Certificates - Withdraw status (more than 60 days overdue)
Certificate::where('next_surveillance', '<', $sixtyDaysAgo)
->where('status', '!=', 'withdraw')
->update(['status' => 'withdraw']);

// Update Certificates - Suspend status (between 1 and 60 days overdue)
Certificate::whereBetween('next_surveillance', [$sixtyDaysAgo, $yesterday])
->where('status', '!=', 'suspend')
->update(['status' => 'suspend']);

// Update Certificates - Active status (Next surveillance date is in the future, up to 45 days)
Certificate::whereDate('next_surveillance', '>=', $today)
->whereDate('next_surveillance', '<=', $fortyFiveDaysLater)
->where('status', '!=', 'active')
->update(['status' => 'active']);
    
        // Status: Active (If next surveillance is more than 45 days away)
        // Certificate::whereDate('next_surveillance', '>', $today->addDays(45))
        //     ->where('status', '!=', 'active')
        //     ->update(['status' => 'active']);
    
        // Sync statuses to Orders
        $certificates = Certificate::all();
    
        foreach ($certificates as $certificate) {
            $order = Order::find($certificate->order_id);
    
            if ($order) {
                $order->status = $certificate->status;
                $order->save();
            }
        }
         // Withdraw status (more than 60 days overdue)
    AssociateClientCertificate::where('next_surveillance', '<', $sixtyDaysAgo)
    ->where('status', '!=', 'withdraw')
    ->update(['status' => 'withdraw']);

// Suspend status (between 1 and 60 days overdue)
AssociateClientCertificate::whereBetween('next_surveillance', [$sixtyDaysAgo, $yesterday])
    ->where('status', '!=', 'suspend')
    ->update(['status' => 'suspend']);

// Active status (Next surveillance date is in the future, up to 45 days)
AssociateClientCertificate::whereDate('next_surveillance', '>=', $today)
    ->whereDate('next_surveillance', '<=', $fortyFiveDaysLater)
    ->where('status', '!=', 'active')
    ->update(['status' => 'active']);

// Sync statuses to Orders for Associate Client Certificates
$associateClientCertificates = AssociateClientCertificate::all();

foreach ($associateClientCertificates as $certificate) {
    $order = Order::find($certificate->order_id);

    if ($order) {
        $order->status = $certificate->status;
        $order->save();
    }
}

    
        $this->info('Certificate and order statuses updated successfully.');
    }
    
}
