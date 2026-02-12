<?php
/**
 * PHP gRPC Client Example for Package Delivery Service
 *
 * This example demonstrates how to interact with the Package Delivery gRPC server
 * from a PHP client application.
 *
 * Prerequisites:
 * - Install gRPC extension: pecl install grpc
 * - Install protobuf extension: pecl install protobuf
 * - Generate client stubs from proto files
 *
 * Usage:
 * php examples/client.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Note: In a real implementation, you would include the generated protobuf classes
// require_once __DIR__ . '/../app/Grpc/Generated/...';

use Grpc\ChannelCredentials;

class PackageDeliveryClient
{
    private $client;

    public function __construct(string $hostname = 'localhost:9001')
    {
        // Create gRPC client
        // $this->client = new PackageDeliveryServiceClient(
        //     $hostname,
        //     ['credentials' => ChannelCredentials::createInsecure()]
        // );

        echo "Package Delivery gRPC Client\n";
        echo "=============================\n\n";
        echo "Server: {$hostname}\n\n";
    }

    /**
     * Example 1: Create a new package
     */
    public function exampleCreatePackage()
    {
        echo "Example 1: Creating a new package\n";
        echo "-----------------------------------\n";

        // In a real implementation:
        // $request = new CreatePackageRequest();
        // $request->setSenderName('John Doe');
        // $request->setSenderAddress('123 Main St, City A');
        // $request->setSenderPhone('+1234567890');
        // $request->setRecipientName('Jane Smith');
        // $request->setRecipientAddress('456 Oak Ave, City B');
        // $request->setRecipientPhone('+0987654321');
        // $request->setWeight(2.5);
        // $request->setDescription('Books and documents');
        // $request->setPackageType(PackageType::EXPRESS);
        //
        // list($response, $status) = $this->client->CreatePackage($request)->wait();
        //
        // if ($status->code === Grpc\STATUS_OK) {
        //     echo "Package created successfully!\n";
        //     echo "Tracking Number: " . $response->getTrackingNumber() . "\n";
        // } else {
        //     echo "Error: " . $status->details . "\n";
        // }

        echo "Request:\n";
        echo "  Sender: John Doe, 123 Main St, City A, +1234567890\n";
        echo "  Recipient: Jane Smith, 456 Oak Ave, City B, +0987654321\n";
        echo "  Weight: 2.5 kg\n";
        echo "  Type: EXPRESS\n\n";

        echo "Expected Response:\n";
        echo "  Tracking Number: PKG12345678\n";
        echo "  Status: PENDING\n\n";
    }

    /**
     * Example 2: Get package details
     */
    public function exampleGetPackage(string $trackingNumber = 'PKG12345678')
    {
        echo "Example 2: Getting package details\n";
        echo "-----------------------------------\n";

        // In a real implementation:
        // $request = new GetPackageRequest();
        // $request->setTrackingNumber($trackingNumber);
        //
        // list($response, $status) = $this->client->GetPackage($request)->wait();
        //
        // if ($status->code === Grpc\STATUS_OK) {
        //     echo "Package Details:\n";
        //     echo "  Tracking Number: " . $response->getTrackingNumber() . "\n";
        //     echo "  Status: " . PackageStatus::name($response->getStatus()) . "\n";
        //     echo "  Sender: " . $response->getSenderName() . "\n";
        //     echo "  Recipient: " . $response->getRecipientName() . "\n";
        //     echo "  Current Location: " . $response->getCurrentLocation() . "\n";
        // } else {
        //     echo "Error: " . $status->details . "\n";
        // }

        echo "Request:\n";
        echo "  Tracking Number: {$trackingNumber}\n\n";

        echo "Expected Response:\n";
        echo "  Full package details with tracking history\n\n";
    }

    /**
     * Example 3: Update package location
     */
    public function exampleUpdateLocation(string $trackingNumber = 'PKG12345678')
    {
        echo "Example 3: Updating package location\n";
        echo "-------------------------------------\n";

        // In a real implementation:
        // $request = new UpdateLocationRequest();
        // $request->setTrackingNumber($trackingNumber);
        // $request->setCurrentLocation('Distribution Center NYC');
        // $request->setLocationDescription('Package arrived at NYC distribution center');
        // $request->setStatus(PackageStatus::IN_TRANSIT);
        //
        // list($response, $status) = $this->client->UpdatePackageLocation($request)->wait();
        //
        // if ($status->code === Grpc\STATUS_OK) {
        //     echo "Location updated successfully!\n";
        //     echo "Current Location: " . $response->getCurrentLocation() . "\n";
        //     echo "Status: " . PackageStatus::name($response->getStatus()) . "\n";
        // } else {
        //     echo "Error: " . $status->details . "\n";
        // }

        echo "Request:\n";
        echo "  Tracking Number: {$trackingNumber}\n";
        echo "  Location: Distribution Center NYC\n";
        echo "  Description: Package arrived at NYC distribution center\n";
        echo "  Status: IN_TRANSIT\n\n";

        echo "Expected Response:\n";
        echo "  Updated package with new location and status\n\n";
    }

    /**
     * Example 4: List packages with pagination
     */
    public function exampleListPackages(int $page = 1, int $perPage = 10)
    {
        echo "Example 4: Listing packages\n";
        echo "---------------------------\n";

        // In a real implementation:
        // $request = new ListPackagesRequest();
        // $request->setPage($page);
        // $request->setPerPage($perPage);
        // $request->setStatusFilter(PackageStatus::IN_TRANSIT);
        //
        // list($response, $status) = $this->client->ListPackages($request)->wait();
        //
        // if ($status->code === Grpc\STATUS_OK) {
        //     echo "Total Packages: " . $response->getTotal() . "\n";
        //     echo "Current Page: " . $response->getCurrentPage() . "\n";
        //     echo "Last Page: " . $response->getLastPage() . "\n\n";
        //
        //     foreach ($response->getPackages() as $package) {
        //         echo "- " . $package->getTrackingNumber() . " | ";
        //         echo PackageStatus::name($package->getStatus()) . " | ";
        //         echo $package->getCurrentLocation() . "\n";
        //     }
        // } else {
        //     echo "Error: " . $status->details . "\n";
        // }

        echo "Request:\n";
        echo "  Page: {$page}\n";
        echo "  Per Page: {$perPage}\n";
        echo "  Filter: IN_TRANSIT\n\n";

        echo "Expected Response:\n";
        echo "  List of packages with pagination info\n\n";
    }

    /**
     * Example 5: Track package (server streaming)
     */
    public function exampleTrackPackage(string $trackingNumber = 'PKG12345678')
    {
        echo "Example 5: Tracking package (streaming)\n";
        echo "----------------------------------------\n";

        // In a real implementation:
        // $request = new TrackPackageRequest();
        // $request->setTrackingNumber($trackingNumber);
        //
        // $stream = $this->client->TrackPackage($request);
        //
        // foreach ($stream->responses() as $update) {
        //     echo "[" . $update->getTimestamp() . "] ";
        //     echo $update->getLocation() . " - ";
        //     echo $update->getDescription() . " (";
        //     echo PackageStatus::name($update->getStatus()) . ")\n";
        // }
        //
        // $status = $stream->getStatus();
        // if ($status->code !== Grpc\STATUS_OK) {
        //     echo "Error: " . $status->details . "\n";
        // }

        echo "Request:\n";
        echo "  Tracking Number: {$trackingNumber}\n\n";

        echo "Expected Response (streaming):\n";
        echo "  [2024-01-01 10:00:00] Package Created - Package has been created (PENDING)\n";
        echo "  [2024-01-01 14:30:00] Warehouse A - Package picked up (PICKED_UP)\n";
        echo "  [2024-01-01 18:45:00] Distribution Center - In transit (IN_TRANSIT)\n";
        echo "  ...\n\n";
    }

    /**
     * Example 6: Cancel package
     */
    public function exampleCancelPackage(string $trackingNumber = 'PKG12345678')
    {
        echo "Example 6: Cancelling package\n";
        echo "------------------------------\n";

        // In a real implementation:
        // $request = new CancelPackageRequest();
        // $request->setTrackingNumber($trackingNumber);
        // $request->setReason('Customer requested cancellation');
        //
        // list($response, $status) = $this->client->CancelPackage($request)->wait();
        //
        // if ($status->code === Grpc\STATUS_OK) {
        //     echo "Package cancelled successfully!\n";
        //     echo "Status: " . PackageStatus::name($response->getStatus()) . "\n";
        // } else {
        //     echo "Error: " . $status->details . "\n";
        // }

        echo "Request:\n";
        echo "  Tracking Number: {$trackingNumber}\n";
        echo "  Reason: Customer requested cancellation\n\n";

        echo "Expected Response:\n";
        echo "  Package with CANCELLED status\n\n";
    }

    /**
     * Run all examples
     */
    public function runAllExamples()
    {
        $this->exampleCreatePackage();
        $this->exampleGetPackage();
        $this->exampleUpdateLocation();
        $this->exampleListPackages();
        $this->exampleTrackPackage();
        $this->exampleCancelPackage();

        echo "\n";
        echo "Note: This is a demonstration client showing the API structure.\n";
        echo "To use with a real server:\n";
        echo "1. Generate client stubs from proto files\n";
        echo "2. Uncomment the implementation code\n";
        echo "3. Ensure the gRPC server is running on localhost:9001\n";
    }
}

// Run the examples
$client = new PackageDeliveryClient();
$client->runAllExamples();
