<?php
/**
 * PHP Client untuk Package Delivery API
 *
 * Client ini memanggil REST API (bukan gRPC langsung), cocok untuk Windows
 * tanpa grpcurl. Pastikan Laravel server berjalan: php artisan serve
 *
 * Usage:
 *   php examples/client.php
 *
 * Base URL default: http://127.0.0.1:8000/api
 */

$baseUrl = $argv[1] ?? 'http://127.0.0.1:8000/api';

function httpRequest(string $method, string $url, ?array $body = null): array
{
    $options = [
        'http' => [
            'method' => $method,
            'header' => "Content-type: application/json\r\nAccept: application/json\r\n",
            'ignore_errors' => true,
        ],
    ];

    if ($body !== null && in_array($method, ['POST', 'PATCH', 'PUT'])) {
        $options['http']['content'] = json_encode($body);
    }

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        return ['error' => 'Connection failed. Pastikan server berjalan: php artisan serve'];
    }

    $data = json_decode($response, true) ?? ['raw' => $response];
    $code = $http_response_header[0] ?? '';
    preg_match('/\d{3}/', $code, $matches);
    $statusCode = (int) ($matches[0] ?? 0);

    return ['data' => $data, 'status' => $statusCode];
}

function printResult(array $result): void
{
    if (isset($result['error'])) {
        echo "ERROR: {$result['error']}\n";
        return;
    }

    $data = $result['data'] ?? [];
    $status = $result['status'] ?? 0;

    if ($status >= 400) {
        echo "HTTP {$status}: " . ($data['error'] ?? json_encode($data)) . "\n";
        return;
    }

    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n";
echo "========================================\n";
echo "  Package Delivery API Client\n";
echo "========================================\n";
echo "Base URL: {$baseUrl}\n";
echo "\n";

$menu = [
    1 => 'Create Package',
    2 => 'Get Package',
    3 => 'List Packages',
    4 => 'Update Location',
    5 => 'Track Package',
    6 => 'Cancel Package',
    0 => 'Exit',
];

while (true) {
    echo "\n--- Menu ---\n";
    foreach ($menu as $key => $label) {
        echo "  {$key}. {$label}\n";
    }
    echo "\nPilih (0-6): ";

    $input = trim(fgets(STDIN) ?: '0');
    $choice = (int) $input;

    if ($choice === 0) {
        echo "Bye!\n";
        break;
    }

    switch ($choice) {
        case 1: // Create Package
            echo "\n--- Create Package ---\n";
            $body = [
                'sender_name' => readInput('Sender name', 'John Doe'),
                'sender_address' => readInput('Sender address', '123 Main St, Jakarta'),
                'sender_phone' => readInput('Sender phone', '+6281234567890'),
                'recipient_name' => readInput('Recipient name', 'Jane Smith'),
                'recipient_address' => readInput('Recipient address', '456 Oak Ave, Bandung'),
                'recipient_phone' => readInput('Recipient phone', '+6289876543210'),
                'weight' => (float) readInput('Weight (kg)', '2.5'),
                'description' => readInput('Description (optional)', 'Books'),
                'package_type' => readInput('Type (STANDARD/EXPRESS/OVERNIGHT/FRAGILE/DOCUMENTS)', 'EXPRESS'),
            ];
            $result = httpRequest('POST', "{$baseUrl}/packages", $body);
            printResult($result);
            break;

        case 2: // Get Package
            $tracking = readInput('Tracking number', '');
            if (empty($tracking)) {
                echo "Tracking number wajib diisi.\n";
                break;
            }
            $result = httpRequest('GET', "{$baseUrl}/packages/" . urlencode($tracking));
            printResult($result);
            break;

        case 3: // List Packages
            $page = readInput('Page', '1');
            $perPage = readInput('Per page', '10');
            $statusFilter = readInput('Status filter (kosong=all, PENDING, IN_TRANSIT, dll)', '');
            $url = "{$baseUrl}/packages?page={$page}&per_page={$perPage}";
            if ($statusFilter !== '') {
                $url .= "&status_filter=" . urlencode($statusFilter);
            }
            $result = httpRequest('GET', $url);
            printResult($result);
            break;

        case 4: // Update Location
            echo "\n--- Update Location ---\n";
            $body = [
                'tracking_number' => readInput('Tracking number', ''),
                'current_location' => readInput('Current location', 'Jakarta Distribution Center'),
                'location_description' => readInput('Description', 'Package arrived at distribution center'),
                'status' => readInput('Status (PICKED_UP, IN_TRANSIT, OUT_FOR_DELIVERY, DELIVERED)', 'IN_TRANSIT'),
            ];
            $result = httpRequest('PATCH', "{$baseUrl}/packages/location", $body);
            printResult($result);
            break;

        case 5: // Track Package
            $tracking = readInput('Tracking number', '');
            if (empty($tracking)) {
                echo "Tracking number wajib diisi.\n";
                break;
            }
            $result = httpRequest('GET', "{$baseUrl}/packages/" . urlencode($tracking) . "/track");
            printResult($result);
            break;

        case 6: // Cancel Package
            echo "\n--- Cancel Package ---\n";
            $body = [
                'tracking_number' => readInput('Tracking number', ''),
                'reason' => readInput('Reason', 'Customer requested cancellation'),
            ];
            $result = httpRequest('POST', "{$baseUrl}/packages/cancel", $body);
            printResult($result);
            break;

        default:
            echo "Pilihan tidak valid.\n";
    }
}

function readInput(string $prompt, string $default = ''): string
{
    $suffix = $default !== '' ? " [{$default}]" : '';
    echo "{$prompt}{$suffix}: ";
    $line = trim(fgets(STDIN) ?: '');
    return $line !== '' ? $line : $default;
}
