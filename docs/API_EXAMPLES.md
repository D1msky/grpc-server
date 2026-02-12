# API Examples

Comprehensive examples for all Package Delivery Service endpoints.

## Table of Contents

1. [CreatePackage](#1-createpackage)
2. [GetPackage](#2-getpackage)
3. [UpdatePackageLocation](#3-updatepackagelocation)
4. [ListPackages](#4-listpackages)
5. [TrackPackage](#5-trackpackage-streaming)
6. [CancelPackage](#6-cancelpackage)

## 1. CreatePackage

Creates a new package for delivery.

### Request

```bash
grpcurl -plaintext -d '{
  "sender_name": "John Doe",
  "sender_address": "123 Main Street, New York, NY 10001",
  "sender_phone": "+1-555-0100",
  "recipient_name": "Jane Smith",
  "recipient_address": "456 Oak Avenue, Los Angeles, CA 90001",
  "recipient_phone": "+1-555-0200",
  "weight": 2.5,
  "description": "Electronics and accessories",
  "package_type": 1
}' localhost:9001 package_delivery.PackageDeliveryService/CreatePackage
```

### Response

```json
{
  "tracking_number": "PKG5F8A91234",
  "sender_name": "John Doe",
  "sender_address": "123 Main Street, New York, NY 10001",
  "sender_phone": "+1-555-0100",
  "recipient_name": "Jane Smith",
  "recipient_address": "456 Oak Avenue, Los Angeles, CA 90001",
  "recipient_phone": "+1-555-0200",
  "weight": 2.5,
  "description": "Electronics and accessories",
  "package_type": 1,
  "status": 0,
  "current_location": "",
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-01-15T10:30:00Z",
  "tracking_history": [
    {
      "location": "Package Created",
      "description": "Package has been created and is pending pickup",
      "status": 0,
      "timestamp": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Package Types

- `0` - STANDARD: Regular delivery (3-5 business days)
- `1` - EXPRESS: Expedited delivery (1-2 business days)
- `2` - OVERNIGHT: Next day delivery
- `3` - FRAGILE: Handle with care
- `4` - DOCUMENTS: Document-only package

---

## 2. GetPackage

Retrieves detailed information about a package.

### Request

```bash
grpcurl -plaintext -d '{
  "tracking_number": "PKG5F8A91234"
}' localhost:9001 package_delivery.PackageDeliveryService/GetPackage
```

### Response

```json
{
  "tracking_number": "PKG5F8A91234",
  "sender_name": "John Doe",
  "sender_address": "123 Main Street, New York, NY 10001",
  "sender_phone": "+1-555-0100",
  "recipient_name": "Jane Smith",
  "recipient_address": "456 Oak Avenue, Los Angeles, CA 90001",
  "recipient_phone": "+1-555-0200",
  "weight": 2.5,
  "description": "Electronics and accessories",
  "package_type": 1,
  "status": 2,
  "current_location": "Distribution Center Chicago",
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-01-16T14:20:00Z",
  "tracking_history": [
    {
      "location": "Distribution Center Chicago",
      "description": "Package in transit",
      "status": 2,
      "timestamp": "2024-01-16T14:20:00Z"
    },
    {
      "location": "Warehouse NYC",
      "description": "Package picked up from sender",
      "status": 1,
      "timestamp": "2024-01-15T15:00:00Z"
    },
    {
      "location": "Package Created",
      "description": "Package has been created and is pending pickup",
      "status": 0,
      "timestamp": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Error Cases

**Package not found:**

```bash
grpcurl -plaintext -d '{
  "tracking_number": "INVALID123"
}' localhost:9001 package_delivery.PackageDeliveryService/GetPackage
```

Response:

```
ERROR:
  Code: NotFound
  Message: Package not found
```

---

## 3. UpdatePackageLocation

Updates the current location and status of a package.

### Request

```bash
grpcurl -plaintext -d '{
  "tracking_number": "PKG5F8A91234",
  "current_location": "Distribution Center Los Angeles",
  "location_description": "Package arrived at final distribution center",
  "status": 3
}' localhost:9001 package_delivery.PackageDeliveryService/UpdatePackageLocation
```

### Response

```json
{
  "tracking_number": "PKG5F8A91234",
  "status": 3,
  "current_location": "Distribution Center Los Angeles",
  "updated_at": "2024-01-17T08:15:00Z",
  "tracking_history": [
    {
      "location": "Distribution Center Los Angeles",
      "description": "Package arrived at final distribution center",
      "status": 3,
      "timestamp": "2024-01-17T08:15:00Z"
    },
    // ... previous history
  ]
}
```

### Package Statuses

- `0` - PENDING: Awaiting pickup
- `1` - PICKED_UP: Collected from sender
- `2` - IN_TRANSIT: On the way
- `3` - OUT_FOR_DELIVERY: Out for final delivery
- `4` - DELIVERED: Successfully delivered
- `5` - CANCELLED: Delivery cancelled
- `6` - FAILED: Delivery attempt failed

---

## 4. ListPackages

Lists all packages with pagination and optional filtering.

### Request - All Packages

```bash
grpcurl -plaintext -d '{
  "page": 1,
  "per_page": 10
}' localhost:9001 package_delivery.PackageDeliveryService/ListPackages
```

### Request - Filter by Status

```bash
grpcurl -plaintext -d '{
  "page": 1,
  "per_page": 10,
  "status_filter": 2
}' localhost:9001 package_delivery.PackageDeliveryService/ListPackages
```

### Response

```json
{
  "packages": [
    {
      "tracking_number": "PKG5F8A91234",
      "sender_name": "John Doe",
      "recipient_name": "Jane Smith",
      "status": 2,
      "current_location": "Distribution Center Chicago",
      // ... full package details
    },
    {
      "tracking_number": "PKG6G9B82345",
      "sender_name": "Alice Johnson",
      "recipient_name": "Bob Williams",
      "status": 2,
      "current_location": "Distribution Center Denver",
      // ... full package details
    }
  ],
  "total": 47,
  "current_page": 1,
  "last_page": 5
}
```

### Pagination Examples

**Page 2:**

```bash
grpcurl -plaintext -d '{
  "page": 2,
  "per_page": 10
}' localhost:9001 package_delivery.PackageDeliveryService/ListPackages
```

**Different page size:**

```bash
grpcurl -plaintext -d '{
  "page": 1,
  "per_page": 25
}' localhost:9001 package_delivery.PackageDeliveryService/ListPackages
```

---

## 5. TrackPackage (Streaming)

Streams all tracking updates for a package in real-time.

### Request

```bash
grpcurl -plaintext -d '{
  "tracking_number": "PKG5F8A91234"
}' localhost:9001 package_delivery.PackageDeliveryService/TrackPackage
```

### Response (Stream)

```json
{
  "tracking_number": "PKG5F8A91234",
  "location": "Package Created",
  "description": "Package has been created and is pending pickup",
  "status": 0,
  "timestamp": "2024-01-15T10:30:00Z"
}
{
  "tracking_number": "PKG5F8A91234",
  "location": "Warehouse NYC",
  "description": "Package picked up from sender",
  "status": 1,
  "timestamp": "2024-01-15T15:00:00Z"
}
{
  "tracking_number": "PKG5F8A91234",
  "location": "Distribution Center Chicago",
  "description": "Package in transit",
  "status": 2,
  "timestamp": "2024-01-16T14:20:00Z"
}
{
  "tracking_number": "PKG5F8A91234",
  "location": "Distribution Center Los Angeles",
  "description": "Package arrived at final distribution center",
  "status": 3,
  "timestamp": "2024-01-17T08:15:00Z"
}
{
  "tracking_number": "PKG5F8A91234",
  "location": "456 Oak Avenue, Los Angeles",
  "description": "Package delivered successfully",
  "status": 4,
  "timestamp": "2024-01-17T16:30:00Z"
}
```

**Note:** This is a server-streaming RPC. Each tracking update is sent as a separate message.

---

## 6. CancelPackage

Cancels a package delivery.

### Request

```bash
grpcurl -plaintext -d '{
  "tracking_number": "PKG5F8A91234",
  "reason": "Customer requested cancellation - wrong address"
}' localhost:9001 package_delivery.PackageDeliveryService/CancelPackage
```

### Response

```json
{
  "tracking_number": "PKG5F8A91234",
  "status": 5,
  "tracking_history": [
    {
      "location": "Distribution Center Chicago",
      "description": "Package cancelled. Reason: Customer requested cancellation - wrong address",
      "status": 5,
      "timestamp": "2024-01-16T16:45:00Z"
    },
    // ... previous history
  ]
}
```

### Error Cases

**Package already delivered:**

```bash
grpcurl -plaintext -d '{
  "tracking_number": "PKG7H1C93456",
  "reason": "Changed mind"
}' localhost:9001 package_delivery.PackageDeliveryService/CancelPackage
```

Response:

```
ERROR:
  Code: FailedPrecondition
  Message: Cannot cancel package with status: DELIVERED
```

**Package not found:**

```
ERROR:
  Code: NotFound
  Message: Package not found
```

---

## Complete Workflow Example

Here's a complete workflow from package creation to delivery:

```bash
# 1. Create a package
TRACKING=$(grpcurl -plaintext -d '{
  "sender_name": "Alice Johnson",
  "sender_address": "789 Pine St, Seattle, WA",
  "sender_phone": "+1-555-0300",
  "recipient_name": "Bob Williams",
  "recipient_address": "321 Elm St, Miami, FL",
  "recipient_phone": "+1-555-0400",
  "weight": 1.2,
  "description": "Books",
  "package_type": 0
}' localhost:9001 package_delivery.PackageDeliveryService/CreatePackage | jq -r '.tracking_number')

echo "Created package: $TRACKING"

# 2. Update: Package picked up
grpcurl -plaintext -d "{
  \"tracking_number\": \"$TRACKING\",
  \"current_location\": \"Seattle Warehouse\",
  \"location_description\": \"Package picked up\",
  \"status\": 1
}" localhost:9001 package_delivery.PackageDeliveryService/UpdatePackageLocation

# 3. Update: In transit
grpcurl -plaintext -d "{
  \"tracking_number\": \"$TRACKING\",
  \"current_location\": \"Distribution Center Denver\",
  \"location_description\": \"In transit\",
  \"status\": 2
}" localhost:9001 package_delivery.PackageDeliveryService/UpdatePackageLocation

# 4. Update: Out for delivery
grpcurl -plaintext -d "{
  \"tracking_number\": \"$TRACKING\",
  \"current_location\": \"Miami Distribution\",
  \"location_description\": \"Out for delivery\",
  \"status\": 3
}" localhost:9001 package_delivery.PackageDeliveryService/UpdatePackageLocation

# 5. Update: Delivered
grpcurl -plaintext -d "{
  \"tracking_number\": \"$TRACKING\",
  \"current_location\": \"321 Elm St, Miami, FL\",
  \"location_description\": \"Delivered successfully\",
  \"status\": 4
}" localhost:9001 package_delivery.PackageDeliveryService/UpdatePackageLocation

# 6. Get final status
grpcurl -plaintext -d "{
  \"tracking_number\": \"$TRACKING\"
}" localhost:9001 package_delivery.PackageDeliveryService/GetPackage

# 7. Stream tracking history
grpcurl -plaintext -d "{
  \"tracking_number\": \"$TRACKING\"
}" localhost:9001 package_delivery.PackageDeliveryService/TrackPackage
```

## Tips

1. **Save tracking numbers** from CreatePackage responses for later use
2. **Use jq** to parse JSON responses: `| jq '.tracking_number'`
3. **Check status codes** to handle errors appropriately
4. **Test pagination** with different `per_page` values
5. **Try server streaming** with TrackPackage to see real-time updates
