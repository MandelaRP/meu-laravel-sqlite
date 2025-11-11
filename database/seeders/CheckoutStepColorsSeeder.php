<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Checkout;
use Illuminate\Database\Seeder;

class CheckoutStepColorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $checkouts = Checkout::whereNull('step_active_color')
            ->orWhereNull('step_completed_color')
            ->orWhereNull('step_inactive_color')
            ->orWhereNull('step_text_color')
            ->orWhereNull('payment_icon_primary_color')
            ->orWhereNull('payment_icon_secondary_color')
            ->orWhereNull('payment_icon_background_color')
            ->get();

        foreach ($checkouts as $checkout) {
            $checkout->update([
                'step_active_color'             => $checkout->step_active_color ?? '#3b82f6',
                'step_completed_color'          => $checkout->step_completed_color ?? '#10b981',
                'step_inactive_color'           => $checkout->step_inactive_color ?? '#9ca3af',
                'step_text_color'               => $checkout->step_text_color ?? '#ffffff',
                'payment_icon_primary_color'    => $checkout->payment_icon_primary_color ?? '#3b82f6',
                'payment_icon_secondary_color'  => $checkout->payment_icon_secondary_color ?? '#1d4ed8',
                'payment_icon_background_color' => $checkout->payment_icon_background_color ?? '#eff6ff',
            ]);
        }

        $this->command->info("Updated {$checkouts->count()} checkouts with default step and payment icon colors.");
    }
}
