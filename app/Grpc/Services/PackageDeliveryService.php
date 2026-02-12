<?php

namespace App\Grpc\Services;

use App\Services\PackageService;
use Spiral\RoadRunner\GRPC;
use Spiral\RoadRunner\GRPC\ContextInterface;

/**
 * Package Delivery gRPC Service Implementation
 *
 * This service handles all package delivery operations including:
 * - Creating new packages
 * - Retrieving package details
 * - Updating package locations
 * - Listing packages with pagination
 * - Real-time package tracking (streaming)
 * - Cancelling packages
 */
class PackageDeliveryService implements PackageDeliveryServiceInterface
{
    private PackageService $packageService;

    public function __construct()
    {
        $this->packageService = new PackageService();
    }

    /**
     * Create a new package for delivery (Unary RPC)
     *
     * @param ContextInterface $ctx Request context
     * @param CreatePackageRequest $in Input request
     * @return PackageResponse
     */
    public function CreatePackage(ContextInterface $ctx, CreatePackageRequest $in): PackageResponse
    {
        try {
            // Validate input
            $this->validateCreatePackageRequest($in);

            $package = $this->packageService->createPackage([
                'sender_name' => $in->getSenderName(),
                'sender_address' => $in->getSenderAddress(),
                'sender_phone' => $in->getSenderPhone(),
                'recipient_name' => $in->getRecipientName(),
                'recipient_address' => $in->getRecipientAddress(),
                'recipient_phone' => $in->getRecipientPhone(),
                'weight' => $in->getWeight(),
                'description' => $in->getDescription(),
                'package_type' => $this->mapPackageType($in->getPackageType()),
            ]);

            return $this->buildPackageResponse($package);
        } catch (\Exception $e) {
            throw new GRPC\Exception\GRPCException(
                $e->getMessage(),
                GRPC\StatusCode::INVALID_ARGUMENT
            );
        }
    }

    /**
     * Get package details by tracking number (Unary RPC)
     *
     * @param ContextInterface $ctx Request context
     * @param GetPackageRequest $in Input request
     * @return PackageResponse
     */
    public function GetPackage(ContextInterface $ctx, GetPackageRequest $in): PackageResponse
    {
        $package = $this->packageService->getPackage($in->getTrackingNumber());

        if (!$package) {
            throw new GRPC\Exception\GRPCException(
                'Package not found',
                GRPC\StatusCode::NOT_FOUND
            );
        }

        return $this->buildPackageResponse($package);
    }

    /**
     * Update package location and status (Unary RPC)
     *
     * @param ContextInterface $ctx Request context
     * @param UpdateLocationRequest $in Input request
     * @return PackageResponse
     */
    public function UpdatePackageLocation(
        ContextInterface $ctx,
        UpdateLocationRequest $in
    ): PackageResponse {
        $package = $this->packageService->updatePackageLocation(
            $in->getTrackingNumber(),
            $in->getCurrentLocation(),
            $in->getLocationDescription(),
            $this->mapPackageStatus($in->getStatus())
        );

        if (!$package) {
            throw new GRPC\Exception\GRPCException(
                'Package not found',
                GRPC\StatusCode::NOT_FOUND
            );
        }

        return $this->buildPackageResponse($package);
    }

    /**
     * List packages with pagination (Unary RPC)
     *
     * @param ContextInterface $ctx Request context
     * @param ListPackagesRequest $in Input request
     * @return ListPackagesResponse
     */
    public function ListPackages(
        ContextInterface $ctx,
        ListPackagesRequest $in
    ): ListPackagesResponse {
        $page = max(1, $in->getPage());
        $perPage = max(1, min(100, $in->getPerPage() ?: 10));
        $statusFilter = $in->getStatusFilter()
            ? $this->mapPackageStatus($in->getStatusFilter())
            : null;

        $paginator = $this->packageService->listPackages($page, $perPage, $statusFilter);

        $response = new ListPackagesResponse();
        $packages = [];

        foreach ($paginator->items() as $package) {
            $packages[] = $this->buildPackageResponse($package);
        }

        $response->setPackages($packages);
        $response->setTotal($paginator->total());
        $response->setCurrentPage($paginator->currentPage());
        $response->setLastPage($paginator->lastPage());

        return $response;
    }

    /**
     * Track package in real-time (Server Streaming RPC)
     *
     * @param ContextInterface $ctx Request context
     * @param TrackPackageRequest $in Input request
     * @return iterable<TrackingUpdate>
     */
    public function TrackPackage(ContextInterface $ctx, TrackPackageRequest $in): iterable
    {
        $trackingUpdates = $this->packageService->getTrackingUpdates(
            $in->getTrackingNumber()
        );

        if ($trackingUpdates->isEmpty()) {
            throw new GRPC\Exception\GRPCException(
                'Package not found',
                GRPC\StatusCode::NOT_FOUND
            );
        }

        foreach ($trackingUpdates as $history) {
            $update = new TrackingUpdate();
            $update->setTrackingNumber($in->getTrackingNumber());
            $update->setLocation($history->location);
            $update->setDescription($history->description);
            $update->setStatus($this->reverseMapPackageStatus($history->status));
            $update->setTimestamp($history->created_at->toIso8601String());

            yield $update;

            // Simulate real-time streaming
            usleep(100000); // 100ms delay
        }
    }

    /**
     * Cancel a package delivery (Unary RPC)
     *
     * @param ContextInterface $ctx Request context
     * @param CancelPackageRequest $in Input request
     * @return PackageResponse
     */
    public function CancelPackage(
        ContextInterface $ctx,
        CancelPackageRequest $in
    ): PackageResponse {
        try {
            $package = $this->packageService->cancelPackage(
                $in->getTrackingNumber(),
                $in->getReason()
            );

            if (!$package) {
                throw new GRPC\Exception\GRPCException(
                    'Package not found',
                    GRPC\StatusCode::NOT_FOUND
                );
            }

            return $this->buildPackageResponse($package);
        } catch (\Exception $e) {
            throw new GRPC\Exception\GRPCException(
                $e->getMessage(),
                GRPC\StatusCode::FAILED_PRECONDITION
            );
        }
    }

    /**
     * Build PackageResponse from Package model
     */
    private function buildPackageResponse($package): PackageResponse
    {
        $response = new PackageResponse();
        $response->setTrackingNumber($package->tracking_number);
        $response->setSenderName($package->sender_name);
        $response->setSenderAddress($package->sender_address);
        $response->setSenderPhone($package->sender_phone);
        $response->setRecipientName($package->recipient_name);
        $response->setRecipientAddress($package->recipient_address);
        $response->setRecipientPhone($package->recipient_phone);
        $response->setWeight($package->weight);
        $response->setDescription($package->description ?? '');
        $response->setPackageType($this->reverseMapPackageType($package->package_type));
        $response->setStatus($this->reverseMapPackageStatus($package->status));
        $response->setCurrentLocation($package->current_location ?? '');
        $response->setCreatedAt($package->created_at->toIso8601String());
        $response->setUpdatedAt($package->updated_at->toIso8601String());

        $trackingHistory = [];
        foreach ($package->trackingHistories as $history) {
            $historyItem = new TrackingHistory();
            $historyItem->setLocation($history->location);
            $historyItem->setDescription($history->description);
            $historyItem->setStatus($this->reverseMapPackageStatus($history->status));
            $historyItem->setTimestamp($history->created_at->toIso8601String());
            $trackingHistory[] = $historyItem;
        }
        $response->setTrackingHistory($trackingHistory);

        return $response;
    }

    /**
     * Validate CreatePackageRequest
     */
    private function validateCreatePackageRequest(CreatePackageRequest $request): void
    {
        $errors = [];

        if (empty($request->getSenderName())) {
            $errors[] = 'Sender name is required';
        }
        if (empty($request->getSenderAddress())) {
            $errors[] = 'Sender address is required';
        }
        if (empty($request->getSenderPhone())) {
            $errors[] = 'Sender phone is required';
        }
        if (empty($request->getRecipientName())) {
            $errors[] = 'Recipient name is required';
        }
        if (empty($request->getRecipientAddress())) {
            $errors[] = 'Recipient address is required';
        }
        if (empty($request->getRecipientPhone())) {
            $errors[] = 'Recipient phone is required';
        }
        if ($request->getWeight() <= 0) {
            $errors[] = 'Weight must be greater than 0';
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
        }
    }

    /**
     * Map proto PackageType enum to string
     */
    private function mapPackageType(int $type): string
    {
        $types = ['STANDARD', 'EXPRESS', 'OVERNIGHT', 'FRAGILE', 'DOCUMENTS'];
        return $types[$type] ?? 'STANDARD';
    }

    /**
     * Reverse map PackageType string to proto enum
     */
    private function reverseMapPackageType(string $type): int
    {
        $types = ['STANDARD' => 0, 'EXPRESS' => 1, 'OVERNIGHT' => 2, 'FRAGILE' => 3, 'DOCUMENTS' => 4];
        return $types[$type] ?? 0;
    }

    /**
     * Map proto PackageStatus enum to string
     */
    private function mapPackageStatus(int $status): string
    {
        $statuses = ['PENDING', 'PICKED_UP', 'IN_TRANSIT', 'OUT_FOR_DELIVERY', 'DELIVERED', 'CANCELLED', 'FAILED'];
        return $statuses[$status] ?? 'PENDING';
    }

    /**
     * Reverse map PackageStatus string to proto enum
     */
    private function reverseMapPackageStatus(string $status): int
    {
        $statuses = [
            'PENDING' => 0,
            'PICKED_UP' => 1,
            'IN_TRANSIT' => 2,
            'OUT_FOR_DELIVERY' => 3,
            'DELIVERED' => 4,
            'CANCELLED' => 5,
            'FAILED' => 6
        ];
        return $statuses[$status] ?? 0;
    }
}
