<?php

namespace App\Http\Controllers;

use App\Services\PackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackageApiController extends Controller
{
    public function __construct(
        private readonly PackageService $packageService
    ) {}

    /**
     * Create a new package.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_address' => 'required|string',
            'sender_phone' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'recipient_address' => 'required|string',
            'recipient_phone' => 'required|string|max:50',
            'weight' => 'required|numeric|min:0.1|max:1000',
            'description' => 'nullable|string',
            'package_type' => 'required|in:STANDARD,EXPRESS,OVERNIGHT,FRAGILE,DOCUMENTS',
        ]);

        $package = $this->packageService->createPackage($validated);

        return response()->json($this->formatPackage($package), 201);
    }

    /**
     * Get package by tracking number.
     */
    public function show(string $trackingNumber): JsonResponse
    {
        $package = $this->packageService->getPackage($trackingNumber);

        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        return response()->json($this->formatPackage($package));
    }

    /**
     * List packages with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $perPage = min((int) $request->get('per_page', 10), 100);
        $statusFilter = $request->get('status_filter');

        $result = $this->packageService->listPackages($page, $perPage, $statusFilter);

        return response()->json([
            'packages' => $result->map(fn ($p) => $this->formatPackage($p)),
            'total' => $result->total(),
            'current_page' => $result->currentPage(),
            'last_page' => $result->lastPage(),
        ]);
    }

    /**
     * Update package location.
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => 'required|string',
            'current_location' => 'required|string',
            'location_description' => 'required|string',
            'status' => 'required|in:PENDING,PICKED_UP,IN_TRANSIT,OUT_FOR_DELIVERY,DELIVERED,CANCELLED,FAILED',
        ]);

        $package = $this->packageService->updatePackageLocation(
            $validated['tracking_number'],
            $validated['current_location'],
            $validated['location_description'],
            $validated['status']
        );

        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        return response()->json($this->formatPackage($package));
    }

    /**
     * Track package (get tracking history).
     */
    public function track(string $trackingNumber): JsonResponse
    {
        $updates = $this->packageService->getTrackingUpdates($trackingNumber);

        return response()->json([
            'tracking_number' => $trackingNumber,
            'updates' => $updates->map(fn ($u) => [
                'location' => $u->location,
                'description' => $u->description,
                'status' => $u->status,
                'timestamp' => $u->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Cancel package.
     */
    public function cancel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => 'required|string',
            'reason' => 'required|string',
        ]);

        try {
            $package = $this->packageService->cancelPackage(
                $validated['tracking_number'],
                $validated['reason']
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        return response()->json($this->formatPackage($package));
    }

    private function formatPackage($package): array
    {
        return [
            'tracking_number' => $package->tracking_number,
            'sender_name' => $package->sender_name,
            'sender_address' => $package->sender_address,
            'sender_phone' => $package->sender_phone,
            'recipient_name' => $package->recipient_name,
            'recipient_address' => $package->recipient_address,
            'recipient_phone' => $package->recipient_phone,
            'weight' => (float) $package->weight,
            'description' => $package->description,
            'package_type' => $package->package_type,
            'status' => $package->status,
            'current_location' => $package->current_location,
            'created_at' => $package->created_at->toIso8601String(),
            'updated_at' => $package->updated_at->toIso8601String(),
            'tracking_history' => $package->relationLoaded('trackingHistories')
                ? $package->trackingHistories->map(fn ($h) => [
                    'location' => $h->location,
                    'description' => $h->description,
                    'status' => $h->status,
                    'timestamp' => $h->created_at->toIso8601String(),
                ])->values()->all()
                : [],
        ];
    }
}
