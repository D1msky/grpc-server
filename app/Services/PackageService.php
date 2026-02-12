<?php

namespace App\Services;

use App\Models\Package;
use App\Models\TrackingHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PackageService
{
    /**
     * Create a new package.
     */
    public function createPackage(array $data): Package
    {
        $data['tracking_number'] = Package::generateTrackingNumber();
        $data['status'] = 'PENDING';

        $package = Package::create($data);

        // Create initial tracking history
        $this->addTrackingHistory(
            $package,
            'Package Created',
            'Package has been created and is pending pickup',
            'PENDING'
        );

        return $package->load('trackingHistories');
    }

    /**
     * Get a package by tracking number.
     */
    public function getPackage(string $trackingNumber): ?Package
    {
        return Package::with('trackingHistories')
            ->where('tracking_number', $trackingNumber)
            ->first();
    }

    /**
     * Update package location and status.
     */
    public function updatePackageLocation(
        string $trackingNumber,
        string $location,
        string $description,
        string $status
    ): ?Package {
        $package = Package::where('tracking_number', $trackingNumber)->first();

        if (!$package) {
            return null;
        }

        $package->update([
            'current_location' => $location,
            'status' => $status,
        ]);

        $this->addTrackingHistory($package, $location, $description, $status);

        return $package->load('trackingHistories');
    }

    /**
     * List packages with pagination and optional status filter.
     */
    public function listPackages(
        int $page = 1,
        int $perPage = 10,
        ?string $statusFilter = null
    ): LengthAwarePaginator {
        $query = Package::with('trackingHistories');

        if ($statusFilter && $statusFilter !== 'ALL') {
            $query->where('status', $statusFilter);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Cancel a package.
     */
    public function cancelPackage(string $trackingNumber, string $reason): ?Package
    {
        $package = Package::where('tracking_number', $trackingNumber)->first();

        if (!$package) {
            return null;
        }

        if (in_array($package->status, ['DELIVERED', 'CANCELLED'])) {
            throw new \Exception("Cannot cancel package with status: {$package->status}");
        }

        $package->update(['status' => 'CANCELLED']);

        $this->addTrackingHistory(
            $package,
            $package->current_location ?? 'N/A',
            "Package cancelled. Reason: {$reason}",
            'CANCELLED'
        );

        return $package->load('trackingHistories');
    }

    /**
     * Get tracking updates for a package (for streaming).
     */
    public function getTrackingUpdates(string $trackingNumber): Collection
    {
        $package = Package::where('tracking_number', $trackingNumber)->first();

        if (!$package) {
            return new Collection();
        }

        return $package->trackingHistories()->orderBy('created_at', 'asc')->get();
    }

    /**
     * Add tracking history entry.
     */
    private function addTrackingHistory(
        Package $package,
        string $location,
        string $description,
        string $status
    ): TrackingHistory {
        return TrackingHistory::create([
            'package_id' => $package->id,
            'location' => $location,
            'description' => $description,
            'status' => $status,
        ]);
    }
}
