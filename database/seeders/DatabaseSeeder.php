<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\TrackingHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Users
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory(9)->create();

        // Packages dengan variasi status
        $packageStatuses = ['PENDING', 'PICKED_UP', 'IN_TRANSIT', 'OUT_FOR_DELIVERY', 'DELIVERED', 'CANCELLED'];
        $packages = collect();

        // 15 packages dengan status acak
        for ($i = 0; $i < 15; $i++) {
            $status = fake()->randomElement($packageStatuses);
            $package = Package::factory()->create([
                'status' => $status,
                'current_location' => in_array($status, ['PENDING']) ? null : fake()->city(),
            ]);
            $packages->push($package);
        }

        // Buat tracking history untuk setiap package (simulasi alur pengiriman)
        $locationFlow = [
            'Jakarta Pusat Warehouse',
            'Jakarta Hub - Processing',
            'Bandung Distribution Center',
            'Surabaya Transit Hub',
            'Local Delivery Center',
        ];

        foreach ($packages as $package) {
            $historyStatuses = ['PENDING', 'PICKED_UP', 'IN_TRANSIT', 'OUT_FOR_DELIVERY', 'DELIVERED'];
            $currentStatusIndex = array_search($package->status, $historyStatuses);

            if ($currentStatusIndex === false) {
                // CANCELLED atau FAILED - buat entry cancel saja
                TrackingHistory::create([
                    'package_id' => $package->id,
                    'location' => $package->current_location ?? 'Origin',
                    'description' => 'Paket dibatalkan atas permintaan pengirim',
                    'status' => $package->status,
                    'created_at' => now()->subDays(rand(0, 2)),
                ]);
                continue;
            }

            $baseTime = now()->subDays(rand(1, 7));
            for ($j = 0; $j <= min($currentStatusIndex, count($locationFlow) - 1); $j++) {
                TrackingHistory::create([
                    'package_id' => $package->id,
                    'location' => $locationFlow[$j] ?? fake()->city(),
                    'description' => $this->getStatusDescription($historyStatuses[$j]),
                    'status' => $historyStatuses[$j],
                    'created_at' => $baseTime->copy()->addHours($j * 12),
                ]);
            }
        }

        $this->command->info('Seeded: 10 users, ' . $packages->count() . ' packages, ' . TrackingHistory::count() . ' tracking histories.');
    }

    private function getStatusDescription(string $status): string
    {
        return match ($status) {
            'PENDING' => 'Paket diterima dan menunggu pengambilan oleh kurir',
            'PICKED_UP' => 'Paket telah diambil oleh kurir dari pengirim',
            'IN_TRANSIT' => 'Paket sedang dalam perjalanan ke tujuan',
            'OUT_FOR_DELIVERY' => 'Paket keluar untuk pengantaran hari ini',
            'DELIVERED' => 'Paket berhasil diterima oleh penerima',
            default => 'Update status paket',
        };
    }
}
