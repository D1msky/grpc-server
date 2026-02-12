<?php

/**
 * RoadRunner gRPC Worker
 *
 * This file bootstraps the Laravel application and initializes the gRPC server.
 * It handles incoming gRPC requests and routes them to the appropriate service handlers.
 */

use Spiral\RoadRunner\GRPC\Server;
use Spiral\Goridge\StreamRelay;
use Spiral\Goridge\RelayInterface;

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel Application
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Initialize gRPC Server
$server = new Server(null, [
    'debug' => env('APP_DEBUG', false),
]);

// Register gRPC Services
// Note: These will be registered after proto files are compiled
// $server->registerService(PackageDeliveryServiceInterface::class, new PackageDeliveryService());

try {
    // Start the worker
    $server->serve();
} catch (\Throwable $e) {
    echo "Worker error: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
