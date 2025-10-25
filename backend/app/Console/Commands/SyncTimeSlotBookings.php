<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\TimeSlot;

class SyncTimeSlotBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timeslots:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync current_bookings for all time slots based on actual appointments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Syncing time slot bookings...');

        // Update current_bookings for all time slots
        DB::statement("
            UPDATE time_slots ts 
            SET current_bookings = (
                SELECT COUNT(*) 
                FROM appointments a 
                WHERE a.time_slot_id = ts.id 
                AND a.status IN ('pending', 'confirmed')
            )
        ");

        // Mark slots as unavailable when fully booked
        $fullyBooked = TimeSlot::whereRaw('current_bookings >= max_capacity')
            ->update(['is_available' => false]);

        // Mark slots as available when there's space
        $available = TimeSlot::whereRaw('current_bookings < max_capacity')
            ->where('date', '>=', now()->toDateString())
            ->update(['is_available' => true]);

        $this->info("✅ Marked {$fullyBooked} slots as unavailable (full)");
        $this->info("✅ Marked {$available} slots as available");

        // Show summary
        $total = TimeSlot::count();
        $availableCount = TimeSlot::where('is_available', true)
            ->where('date', '>=', now()->toDateString())
            ->count();
        $partiallyBooked = TimeSlot::where('current_bookings', '>', 0)
            ->whereRaw('current_bookings < max_capacity')
            ->where('date', '>=', now()->toDateString())
            ->count();

        $this->newLine();
        $this->info("📊 Summary:");
        $this->line("  Total slots: {$total}");
        $this->line("  Available slots (today onwards): {$availableCount}");
        $this->line("  Partially booked: {$partiallyBooked}");

        // Show some examples
        $examples = TimeSlot::where('current_bookings', '>', 0)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        if ($examples->count() > 0) {
            $this->newLine();
            $this->info("📅 Example booked slots:");
            foreach ($examples as $slot) {
                $remaining = $slot->remaining_capacity;
                $emoji = $remaining === 0 ? '🔴' : ($remaining <= 2 ? '🟠' : '🟢');
                $this->line("  {$emoji} {$slot->date} {$slot->start_time->format('H:i')} - {$slot->current_bookings}/{$slot->max_capacity} booked (Còn {$remaining} chỗ)");
            }
        }

        $this->newLine();
        $this->info('✅ Sync completed!');

        return Command::SUCCESS;
    }
}

