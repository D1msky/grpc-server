# gRPC Package Delivery Server - Laravel

A robust gRPC server implementation in Laravel for package delivery tracking system. This project demonstrates best practices for building production-ready gRPC services with PHP.

## Features

- **6 gRPC Endpoints** covering different RPC patterns:
  - `CreatePackage` - Create new delivery packages (Unary RPC)
  - `GetPackage` - Retrieve package details (Unary RPC)
  - `UpdatePackageLocation` - Update package location and status (Unary RPC)
  - `ListPackages` - List packages with pagination (Unary RPC)
  - `TrackPackage` - Real-time tracking updates (Server Streaming RPC)
  - `CancelPackage` - Cancel package delivery (Unary RPC)

- **Robust Architecture**:
  - Service layer separation for business logic
  - Eloquent ORM for database operations
  - Comprehensive error handling and validation
  - Soft deletes for data integrity
  - Database relationships (Package has many TrackingHistories)

- **Production Ready**:
  - RoadRunner as high-performance application server
  - Database migrations and models
  - Proper indexing for performance
  - Logging and monitoring endpoints
  - Health check and metrics

## Technology Stack

- **Laravel 12** - PHP Framework
- **RoadRunner** - High-performance PHP application server with gRPC support
- **Protocol Buffers** - Interface definition language
- **MySQL/SQLite** - Database (configurable)
- **gRPC** - Remote procedure call framework

## Prerequisites

Before you begin, ensure you have the following installed:

- PHP 8.2 or higher
- Composer
- Protocol Buffers Compiler (protoc)
- gRPC PHP extension
- MySQL or SQLite

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd grpc-server
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=grpc_packages
DB_USERNAME=root
DB_PASSWORD=
```

For SQLite (simpler for local development):

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Install RoadRunner

```bash
./vendor/bin/rr get-binary
```

This will download the RoadRunner binary to your project root.

### 6. Generate Proto Files (Optional)

If you have `protoc` and `grpc_php_plugin` installed:

```bash
chmod +x generate_proto.sh
./generate_proto.sh
```

Otherwise, the generated files are already included in the repository.

## Running the Server

### Start the gRPC Server

```bash
./rr serve
```

Or using the Makefile:

```bash
make server-start
```

The server will start on:
- **gRPC**: `localhost:9001`
- **Health Check**: `localhost:2114`
- **Metrics**: `localhost:2112`

### Verify Server is Running

```bash
curl http://localhost:2114/health
```

## Project Structure

```
grpc-server/
├── app/
│   ├── Grpc/
│   │   ├── Services/          # gRPC service implementations
│   │   └── Generated/         # Auto-generated protobuf files
│   ├── Models/                # Eloquent models
│   │   ├── Package.php
│   │   └── TrackingHistory.php
│   └── Services/              # Business logic layer
│       └── PackageService.php
├── database/
│   └── migrations/            # Database migrations
├── proto/
│   └── package_delivery.proto # Protocol buffer definitions
├── examples/                  # Client examples
│   ├── client.php            # PHP client example
│   └── client.py             # Python client example
├── .rr.yaml                  # RoadRunner configuration
├── worker.php                # gRPC worker entry point
└── README.md
```

## API Documentation

### 1. CreatePackage

Creates a new package for delivery.

**Request:**
```protobuf
message CreatePackageRequest {
  string sender_name = 1;
  string sender_address = 2;
  string sender_phone = 3;
  string recipient_name = 4;
  string recipient_address = 5;
  string recipient_phone = 6;
  double weight = 7;
  string description = 8;
  PackageType package_type = 9;
}
```

**Response:** `PackageResponse` with tracking number

### 2. GetPackage

Retrieves package details by tracking number.

**Request:**
```protobuf
message GetPackageRequest {
  string tracking_number = 1;
}
```

**Response:** `PackageResponse` with full package details and tracking history

### 3. UpdatePackageLocation

Updates the current location and status of a package.

**Request:**
```protobuf
message UpdateLocationRequest {
  string tracking_number = 1;
  string current_location = 2;
  string location_description = 3;
  PackageStatus status = 4;
}
```

**Response:** `PackageResponse` with updated information

### 4. ListPackages

Lists all packages with pagination support.

**Request:**
```protobuf
message ListPackagesRequest {
  int32 page = 1;
  int32 per_page = 2;
  PackageStatus status_filter = 3; // optional
}
```

**Response:**
```protobuf
message ListPackagesResponse {
  repeated PackageResponse packages = 1;
  int32 total = 2;
  int32 current_page = 3;
  int32 last_page = 4;
}
```

### 5. TrackPackage (Server Streaming)

Streams all tracking updates for a package in real-time.

**Request:**
```protobuf
message TrackPackageRequest {
  string tracking_number = 1;
}
```

**Response:** Stream of `TrackingUpdate` messages

### 6. CancelPackage

Cancels a package delivery.

**Request:**
```protobuf
message CancelPackageRequest {
  string tracking_number = 1;
  string reason = 2;
}
```

**Response:** `PackageResponse` with cancelled status

## Enums

### PackageType
- `STANDARD (0)` - Standard delivery
- `EXPRESS (1)` - Express delivery
- `OVERNIGHT (2)` - Overnight delivery
- `FRAGILE (3)` - Fragile items
- `DOCUMENTS (4)` - Documents only

### PackageStatus
- `PENDING (0)` - Package created, awaiting pickup
- `PICKED_UP (1)` - Package picked up
- `IN_TRANSIT (2)` - Package in transit
- `OUT_FOR_DELIVERY (3)` - Out for delivery
- `DELIVERED (4)` - Successfully delivered
- `CANCELLED (5)` - Delivery cancelled
- `FAILED (6)` - Delivery failed

## Usage Examples

### Using grpcurl

#### Create a Package

```bash
grpcurl -plaintext -d '{
  "sender_name": "John Doe",
  "sender_address": "123 Main St, City A",
  "sender_phone": "+1234567890",
  "recipient_name": "Jane Smith",
  "recipient_address": "456 Oak Ave, City B",
  "recipient_phone": "+0987654321",
  "weight": 2.5,
  "description": "Books and documents",
  "package_type": 1
}' localhost:9001 package_delivery.PackageDeliveryService/CreatePackage
```

#### Get Package Details

```bash
grpcurl -plaintext -d '{
  "tracking_number": "PKG12345678"
}' localhost:9001 package_delivery.PackageDeliveryService/GetPackage
```

#### Update Package Location

```bash
grpcurl -plaintext -d '{
  "tracking_number": "PKG12345678",
  "current_location": "Distribution Center NYC",
  "location_description": "Package arrived at NYC distribution center",
  "status": 2
}' localhost:9001 package_delivery.PackageDeliveryService/UpdatePackageLocation
```

#### List Packages

```bash
grpcurl -plaintext -d '{
  "page": 1,
  "per_page": 10,
  "status_filter": 2
}' localhost:9001 package_delivery.PackageDeliveryService/ListPackages
```

#### Track Package (Streaming)

```bash
grpcurl -plaintext -d '{
  "tracking_number": "PKG12345678"
}' localhost:9001 package_delivery.PackageDeliveryService/TrackPackage
```

#### Cancel Package

```bash
grpcurl -plaintext -d '{
  "tracking_number": "PKG12345678",
  "reason": "Customer requested cancellation"
}' localhost:9001 package_delivery.PackageDeliveryService/CancelPackage
```

### Using PHP Client

See `examples/client.php` for a complete PHP client implementation.

```bash
php examples/client.php
```

### Using Python Client

See `examples/client.py` for a complete Python client implementation.

```bash
python examples/client.py
```

## Development

### Using Makefile

The project includes a Makefile for common tasks:

```bash
make help              # Show available commands
make install           # Install dependencies and setup
make proto-generate    # Generate proto files
make server-start      # Start gRPC server
make server-stop       # Stop gRPC server
make db-migrate        # Run migrations
make db-fresh          # Fresh database with seeders
make test              # Run tests
```

### Running Tests

```bash
php artisan test
```

Or using the Makefile:

```bash
make test
```

## Database Schema

### Packages Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| tracking_number | string | Unique tracking number |
| sender_name | string | Sender's name |
| sender_address | text | Sender's address |
| sender_phone | string | Sender's phone |
| recipient_name | string | Recipient's name |
| recipient_address | text | Recipient's address |
| recipient_phone | string | Recipient's phone |
| weight | decimal(8,2) | Package weight in kg |
| description | text | Package description |
| package_type | enum | Type of package |
| status | enum | Current status |
| current_location | string | Current location |
| created_at | timestamp | Creation time |
| updated_at | timestamp | Last update time |
| deleted_at | timestamp | Soft delete time |

### Tracking Histories Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| package_id | bigint | Foreign key to packages |
| location | string | Location name |
| description | text | Update description |
| status | enum | Status at this point |
| created_at | timestamp | Event time |
| updated_at | timestamp | Last update |

## Error Handling

The server implements comprehensive error handling:

- **INVALID_ARGUMENT**: Invalid input data
- **NOT_FOUND**: Package not found
- **FAILED_PRECONDITION**: Operation not allowed in current state
- **INTERNAL**: Internal server errors

All errors include descriptive messages to help with debugging.

## Performance Considerations

- Database queries are optimized with proper indexing
- Eager loading used to prevent N+1 queries
- Connection pooling via RoadRunner
- Pagination for list operations
- Soft deletes for data integrity without performance impact

## Security

- Input validation on all endpoints
- SQL injection prevention via Eloquent ORM
- Environment-based configuration
- No sensitive data in logs

## Monitoring

### Health Check

```bash
curl http://localhost:2114/health
```

### Metrics

```bash
curl http://localhost:2112/metrics
```

## Troubleshooting

### Server won't start

1. Check if port 9001 is available:
   ```bash
   lsof -i :9001
   ```

2. Verify RoadRunner binary exists:
   ```bash
   ls -la rr
   ```

3. Check logs in `.rr.yaml` output

### Database connection errors

1. Verify database configuration in `.env`
2. Ensure database exists and is accessible
3. Run migrations: `php artisan migrate`

### Proto generation fails

1. Install protoc: https://grpc.io/docs/protoc-installation/
2. Install gRPC plugin: https://grpc.io/docs/languages/php/quickstart/
3. Run: `./generate_proto.sh`

## Learning Resources

This project is designed to teach:

1. **Unary RPC Pattern**: Single request, single response (CreatePackage, GetPackage, etc.)
2. **Server Streaming RPC**: Single request, stream of responses (TrackPackage)
3. **Error Handling**: Proper gRPC status codes and error messages
4. **Data Validation**: Input validation and sanitization
5. **Database Design**: Relationships, migrations, and Eloquent ORM
6. **Service Architecture**: Separation of concerns with service layer
7. **API Design**: RESTful principles applied to gRPC

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

MIT License

## Support

For issues and questions:
- Create an issue on GitHub
- Check the troubleshooting section
- Review the gRPC documentation

## Acknowledgments

- Laravel Framework
- Spiral Scout (RoadRunner)
- gRPC Team
- Protocol Buffers Team
