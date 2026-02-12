# Quick Start Guide

Get your gRPC Package Delivery Server up and running in 5 minutes!

## Prerequisites

- PHP 8.2+
- Composer
- SQLite (or MySQL)

## Installation Steps

### 1. Install Dependencies

```bash
composer install
```

### 2. Set Up Environment

```bash
cp .env.example .env
php artisan key:generate
```

For SQLite (easiest option):

```bash
# Create database file
touch database/database.sqlite

# Update .env
sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Download RoadRunner

```bash
./vendor/bin/rr get-binary
chmod +x rr
```

### 5. Start the Server

```bash
./rr serve
```

You should see:

```
RoadRunner server started
gRPC server listening on: tcp://0.0.0.0:9001
```

## Testing the Server

### Using grpcurl

Install grpcurl:

```bash
# On macOS
brew install grpcurl

# On Linux
wget https://github.com/fullstorydev/grpcurl/releases/download/v1.8.9/grpcurl_1.8.9_linux_x86_64.tar.gz
tar -xvf grpcurl_1.8.9_linux_x86_64.tar.gz
sudo mv grpcurl /usr/local/bin/
```

Test the server:

```bash
# List services
grpcurl -plaintext localhost:9001 list

# Create a package
grpcurl -plaintext -d '{
  "sender_name": "John Doe",
  "sender_address": "123 Main St",
  "sender_phone": "+1234567890",
  "recipient_name": "Jane Smith",
  "recipient_address": "456 Oak Ave",
  "recipient_phone": "+0987654321",
  "weight": 2.5,
  "description": "Test package",
  "package_type": 0
}' localhost:9001 package_delivery.PackageDeliveryService/CreatePackage
```

## Common Issues

### Port 9001 already in use

```bash
# Find and kill the process
lsof -ti:9001 | xargs kill -9
```

### RoadRunner binary not found

```bash
# Re-download
./vendor/bin/rr get-binary
```

### Database connection error

```bash
# For SQLite, ensure file exists
touch database/database.sqlite

# For MySQL, create database
mysql -u root -p -e "CREATE DATABASE grpc_packages;"
```

## Next Steps

1. Read the [API Documentation](../README.md#api-documentation)
2. Try the [Example Clients](../examples/)
3. Explore the [Database Schema](../README.md#database-schema)
4. Learn about [Error Handling](../README.md#error-handling)

## Quick Command Reference

```bash
# Start server
./rr serve

# Stop server
./rr stop

# Run migrations
php artisan migrate

# Fresh database
php artisan migrate:fresh

# Run tests
php artisan test
```

## Support

If you encounter issues:

1. Check the [Troubleshooting](../README.md#troubleshooting) section
2. Verify all prerequisites are installed
3. Ensure ports 9001, 2114, 2112 are available
4. Check RoadRunner logs for errors
