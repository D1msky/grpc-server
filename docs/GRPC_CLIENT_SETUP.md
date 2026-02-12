# Laravel gRPC Client – Setup untuk Memanggil Package Delivery Server

Dokumen ini menjelaskan apa yang perlu dibuat di Laravel baru untuk memanggil semua gRPC endpoint di project grpc-server ini.

---

## Prerequisites

- PHP 8.2+ dengan ekstensi `grpc` dan `protobuf` (PECL)
- `protoc` (Protocol Buffers compiler)
- `grpc_php_plugin` untuk generate PHP stubs

**Windows:** Download `protoc` dan `grpc_php_plugin` dari [grpc.io](https://grpc.io) atau gunakan WSL.

---

## 1. Buat Laravel Project

```bash
composer create-project laravel/laravel grpc-client
cd grpc-client
```

---

## 2. Install Dependencies

```bash
composer require grpc/grpc google/protobuf
```

---

## 3. Copy Proto & Generate Client Stubs

1. Copy file `proto/package_delivery.proto` dari grpc-server ke `grpc-client/proto/`
2. Buat direktori: `app/Grpc/Generated`
3. Jalankan generate (sesuaikan path `grpc_php_plugin`):

**Linux/Mac:**
```bash
protoc --proto_path=proto \
    --php_out=app/Grpc/Generated \
    --grpc_out=app/Grpc/Generated \
    --plugin=protoc-gen-grpc=$(which grpc_php_plugin) \
    proto/package_delivery.proto
```

**Windows:**
```powershell
protoc --proto_path=proto --php_out=app\Grpc\Generated --grpc_out=app\Grpc\Generated --plugin=protoc-gen-grpc=C:\path\to\grpc_php_plugin.exe proto/package_delivery.proto
```

4. Tambahkan ke `composer.json` autoload jika perlu:
```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "App\\Grpc\\Generated\\": "app/Grpc/Generated/"
    }
}
```
Lalu jalankan `composer dump-autoload`

---

## 4. Config gRPC Server

Di `.env`:

```env
GRPC_SERVER_HOST=127.0.0.1:9001
```

Di `config/services.php`:

```php
'grpc' => [
    'host' => env('GRPC_SERVER_HOST', '127.0.0.1:9001'),
],
```

---

## 5. gRPC Endpoint yang Harus Dihit

| # | RPC Method | Request | Response | Type |
|---|------------|---------|----------|------|
| 1 | CreatePackage | CreatePackageRequest | PackageResponse | Unary |
| 2 | GetPackage | GetPackageRequest | PackageResponse | Unary |
| 3 | UpdatePackageLocation | UpdateLocationRequest | PackageResponse | Unary |
| 4 | ListPackages | ListPackagesRequest | ListPackagesResponse | Unary |
| 5 | TrackPackage | TrackPackageRequest | stream TrackingUpdate | Server Streaming |
| 6 | CancelPackage | CancelPackageRequest | PackageResponse | Unary |

---

## 6. Request/Response per Endpoint

### 1. CreatePackage

**Request (CreatePackageRequest):**
- `sender_name` (string)
- `sender_address` (string)
- `sender_phone` (string)
- `recipient_name` (string)
- `recipient_address` (string)
- `recipient_phone` (string)
- `weight` (double, kg)
- `description` (string, optional)
- `package_type` (int: 0–4, lihat Enum)

**Response:** `PackageResponse`

---

### 2. GetPackage

**Request (GetPackageRequest):**
- `tracking_number` (string)

**Response:** `PackageResponse`

---

### 3. UpdatePackageLocation

**Request (UpdateLocationRequest):**
- `tracking_number` (string)
- `current_location` (string)
- `location_description` (string)
- `status` (int: 0–6, lihat Enum)

**Response:** `PackageResponse`

---

### 4. ListPackages

**Request (ListPackagesRequest):**
- `page` (int)
- `per_page` (int)
- `status_filter` (int, optional: 0–6, atau kosong untuk semua)

**Response (ListPackagesResponse):**
- `packages` (repeated PackageResponse)
- `total` (int)
- `current_page` (int)
- `last_page` (int)

---

### 5. TrackPackage (Server Streaming)

**Request (TrackPackageRequest):**
- `tracking_number` (string)

**Response:** Stream of `TrackingUpdate`:
- `tracking_number`
- `location`
- `description`
- `status`
- `timestamp`

---

### 6. CancelPackage

**Request (CancelPackageRequest):**
- `tracking_number` (string)
- `reason` (string)

**Response:** `PackageResponse`

---

## 7. Enum Reference

**PackageType:**
| Value | Enum |
|-------|------|
| 0 | STANDARD |
| 1 | EXPRESS |
| 2 | OVERNIGHT |
| 3 | FRAGILE |
| 4 | DOCUMENTS |

**PackageStatus:**
| Value | Enum |
|-------|------|
| 0 | PENDING |
| 1 | PICKED_UP |
| 2 | IN_TRANSIT |
| 3 | OUT_FOR_DELIVERY |
| 4 | DELIVERED |
| 5 | CANCELLED |
| 6 | FAILED |

---

## 8. Contoh Kode Pemanggilan (PHP)

### CreatePackage
```php
$request = new CreatePackageRequest();
$request->setSenderName('John Doe');
$request->setSenderAddress('123 Main St');
$request->setSenderPhone('+6281234567890');
$request->setRecipientName('Jane Smith');
$request->setRecipientAddress('456 Oak Ave');
$request->setRecipientPhone('+6289876543210');
$request->setWeight(2.5);
$request->setDescription('Books');
$request->setPackageType(1); // EXPRESS

list($response, $status) = $client->CreatePackage($request)->wait();
if ($status->code === \Grpc\STATUS_OK) {
    $trackingNumber = $response->getTrackingNumber();
}
```

### GetPackage
```php
$request = new GetPackageRequest();
$request->setTrackingNumber('PKG12345678');
list($response, $status) = $client->GetPackage($request)->wait();
```

### UpdatePackageLocation
```php
$request = new UpdateLocationRequest();
$request->setTrackingNumber('PKG12345678');
$request->setCurrentLocation('Jakarta Hub');
$request->setLocationDescription('Arrived at hub');
$request->setStatus(2); // IN_TRANSIT
list($response, $status) = $client->UpdatePackageLocation($request)->wait();
```

### ListPackages
```php
$request = new ListPackagesRequest();
$request->setPage(1);
$request->setPerPage(10);
$request->setStatusFilter(2); // IN_TRANSIT, atau 0 untuk semua
list($response, $status) = $client->ListPackages($request)->wait();
$packages = $response->getPackages();
$total = $response->getTotal();
```

### TrackPackage (Streaming)
```php
$request = new TrackPackageRequest();
$request->setTrackingNumber('PKG12345678');
$call = $client->TrackPackage($request);
foreach ($call->responses() as $update) {
    echo $update->getTimestamp() . ' | ' . $update->getLocation() . ' - ' . $update->getDescription() . "\n";
}
$call->wait();
```

### CancelPackage
```php
$request = new CancelPackageRequest();
$request->setTrackingNumber('PKG12345678');
$request->setReason('Customer requested');
list($response, $status) = $client->CancelPackage($request)->wait();
```

---

## 9. Inisialisasi Client

```php
use App\Grpc\Generated\PackageDeliveryServiceClient;
use Grpc\ChannelCredentials;

$host = config('services.grpc.host', '127.0.0.1:9001');
$client = new PackageDeliveryServiceClient(
    $host,
    ['credentials' => ChannelCredentials::createInsecure()]
);
```

---

## 10. Server Harus Jalan

Pastikan grpc-server berjalan sebelum test client:

```bash
# Di project grpc-server
./rr serve
```

Server gRPC listen di `127.0.0.1:9001`.
