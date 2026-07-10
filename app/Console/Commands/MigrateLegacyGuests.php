<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VipSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MigrateLegacyGuests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vip:migrate-legacy-guests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy VIP subscriptions without user_ids to proper User accounts.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of legacy guests...');

        $subscriptions = VipSubscription::whereNull('user_id')->get();
        $count = 0;

        foreach ($subscriptions as $sub) {
            $user = User::where('email', $sub->email)->first();
            
            if (!$user) {
                // Determine a valid name if it's somehow missing
                $name = !empty($sub->name) ? $sub->name : 'Legacy Guest';
                
                $user = User::create([
                    'name' => $name,
                    'email' => $sub->email,
                    'password' => Hash::make(Str::random(16)),
                ]);
            }
            
            $sub->update(['user_id' => $user->id]);
            $count++;
            
            $this->line("Migrated: {$sub->email} -> User ID: {$user->id}");
        }

        $this->info("Successfully migrated {$count} legacy subscriptions to Users.");
    }
}
