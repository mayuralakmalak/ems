<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixUserPhoneCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:user-phone-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix existing user phone numbers by extracting phone codes and storing them separately';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix user phone codes...');
        
        $users = User::whereNotNull('mobile_number')
            ->orWhereNotNull('phone_number')
            ->get();
        
        $updated = 0;
        
        foreach ($users as $user) {
            $updatedFields = [];
            
            // Process mobile_number
            if ($user->mobile_number && !$user->mobile_number_phone_code) {
                $result = $this->extractPhoneCode($user->mobile_number);
                if ($result) {
                    $updatedFields['mobile_number'] = $result['number'];
                    $updatedFields['mobile_number_phone_code'] = $result['code'];
                }
            }
            
            // Process phone_number
            if ($user->phone_number && !$user->phone_number_phone_code) {
                $result = $this->extractPhoneCode($user->phone_number);
                if ($result) {
                    $updatedFields['phone_number'] = $result['number'];
                    $updatedFields['phone_number_phone_code'] = $result['code'];
                }
            }
            
            if (!empty($updatedFields)) {
                $user->update($updatedFields);
                $updated++;
                $this->line("Updated user: {$user->email}");
            }
        }
        
        $this->info("Completed! Updated {$updated} users.");
        
        return Command::SUCCESS;
    }
    
    /**
     * Extract phone code from a phone number
     * 
     * @param string $phoneNumber
     * @return array|null Returns ['code' => '+91', 'number' => '1234567890'] or null
     */
    private function extractPhoneCode($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return null;
        }
        
        // Remove all spaces and special characters except +
        $cleaned = preg_replace('/[^\d+]/', '', $phoneNumber);
        
        // Check if it starts with +
        if (str_starts_with($cleaned, '+')) {
            // Extract code (1-4 digits after +)
            // Common phone codes are 1-4 digits
            if (preg_match('/^\+(\d{1,4})(\d+)$/', $cleaned, $matches)) {
                return [
                    'code' => '+' . $matches[1],
                    'number' => $matches[2]
                ];
            }
        }
        
        // If no + found, return the number as-is with no code
        return [
            'code' => null,
            'number' => preg_replace('/[^\d]/', '', $phoneNumber)
        ];
    }
}
