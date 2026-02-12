#!/usr/bin/env python3
"""
Python gRPC Client Example for Package Delivery Service

This example demonstrates how to interact with the Package Delivery gRPC server
from a Python client application.

Prerequisites:
- Install gRPC: pip install grpcio grpcio-tools
- Generate client stubs: python -m grpc_tools.protoc -I../proto --python_out=. --grpc_python_out=. ../proto/package_delivery.proto

Usage:
python examples/client.py
"""

import grpc
# from package_delivery_pb2 import (
#     CreatePackageRequest,
#     GetPackageRequest,
#     UpdateLocationRequest,
#     ListPackagesRequest,
#     TrackPackageRequest,
#     CancelPackageRequest,
#     PackageType,
#     PackageStatus
# )
# from package_delivery_pb2_grpc import PackageDeliveryServiceStub


class PackageDeliveryClient:
    """Client for Package Delivery gRPC Service"""

    def __init__(self, host='localhost:9001'):
        """Initialize the gRPC client"""
        # In a real implementation:
        # self.channel = grpc.insecure_channel(host)
        # self.stub = PackageDeliveryServiceStub(self.channel)

        print("Package Delivery gRPC Client")
        print("=" * 40)
        print(f"\nServer: {host}\n")

    def example_create_package(self):
        """Example 1: Create a new package"""
        print("Example 1: Creating a new package")
        print("-" * 40)

        # In a real implementation:
        # request = CreatePackageRequest(
        #     sender_name="John Doe",
        #     sender_address="123 Main St, City A",
        #     sender_phone="+1234567890",
        #     recipient_name="Jane Smith",
        #     recipient_address="456 Oak Ave, City B",
        #     recipient_phone="+0987654321",
        #     weight=2.5,
        #     description="Books and documents",
        #     package_type=PackageType.EXPRESS
        # )
        #
        # try:
        #     response = self.stub.CreatePackage(request)
        #     print(f"Package created successfully!")
        #     print(f"Tracking Number: {response.tracking_number}")
        #     print(f"Status: {PackageStatus.Name(response.status)}")
        # except grpc.RpcError as e:
        #     print(f"Error: {e.details()}")

        print("Request:")
        print("  Sender: John Doe, 123 Main St, City A, +1234567890")
        print("  Recipient: Jane Smith, 456 Oak Ave, City B, +0987654321")
        print("  Weight: 2.5 kg")
        print("  Type: EXPRESS\n")

        print("Expected Response:")
        print("  Tracking Number: PKG12345678")
        print("  Status: PENDING\n")

    def example_get_package(self, tracking_number='PKG12345678'):
        """Example 2: Get package details"""
        print("Example 2: Getting package details")
        print("-" * 40)

        # In a real implementation:
        # request = GetPackageRequest(tracking_number=tracking_number)
        #
        # try:
        #     response = self.stub.GetPackage(request)
        #     print("Package Details:")
        #     print(f"  Tracking Number: {response.tracking_number}")
        #     print(f"  Status: {PackageStatus.Name(response.status)}")
        #     print(f"  Sender: {response.sender_name}")
        #     print(f"  Recipient: {response.recipient_name}")
        #     print(f"  Current Location: {response.current_location}")
        #     print(f"\nTracking History:")
        #     for history in response.tracking_history:
        #         print(f"  [{history.timestamp}] {history.location} - {history.description}")
        # except grpc.RpcError as e:
        #     print(f"Error: {e.details()}")

        print("Request:")
        print(f"  Tracking Number: {tracking_number}\n")

        print("Expected Response:")
        print("  Full package details with tracking history\n")

    def example_update_location(self, tracking_number='PKG12345678'):
        """Example 3: Update package location"""
        print("Example 3: Updating package location")
        print("-" * 40)

        # In a real implementation:
        # request = UpdateLocationRequest(
        #     tracking_number=tracking_number,
        #     current_location="Distribution Center NYC",
        #     location_description="Package arrived at NYC distribution center",
        #     status=PackageStatus.IN_TRANSIT
        # )
        #
        # try:
        #     response = self.stub.UpdatePackageLocation(request)
        #     print("Location updated successfully!")
        #     print(f"Current Location: {response.current_location}")
        #     print(f"Status: {PackageStatus.Name(response.status)}")
        # except grpc.RpcError as e:
        #     print(f"Error: {e.details()}")

        print("Request:")
        print(f"  Tracking Number: {tracking_number}")
        print("  Location: Distribution Center NYC")
        print("  Description: Package arrived at NYC distribution center")
        print("  Status: IN_TRANSIT\n")

        print("Expected Response:")
        print("  Updated package with new location and status\n")

    def example_list_packages(self, page=1, per_page=10):
        """Example 4: List packages with pagination"""
        print("Example 4: Listing packages")
        print("-" * 40)

        # In a real implementation:
        # request = ListPackagesRequest(
        #     page=page,
        #     per_page=per_page,
        #     status_filter=PackageStatus.IN_TRANSIT
        # )
        #
        # try:
        #     response = self.stub.ListPackages(request)
        #     print(f"Total Packages: {response.total}")
        #     print(f"Current Page: {response.current_page}")
        #     print(f"Last Page: {response.last_page}\n")
        #
        #     for package in response.packages:
        #         print(f"- {package.tracking_number} | "
        #               f"{PackageStatus.Name(package.status)} | "
        #               f"{package.current_location}")
        # except grpc.RpcError as e:
        #     print(f"Error: {e.details()}")

        print("Request:")
        print(f"  Page: {page}")
        print(f"  Per Page: {per_page}")
        print("  Filter: IN_TRANSIT\n")

        print("Expected Response:")
        print("  List of packages with pagination info\n")

    def example_track_package(self, tracking_number='PKG12345678'):
        """Example 5: Track package (server streaming)"""
        print("Example 5: Tracking package (streaming)")
        print("-" * 40)

        # In a real implementation:
        # request = TrackPackageRequest(tracking_number=tracking_number)
        #
        # try:
        #     for update in self.stub.TrackPackage(request):
        #         print(f"[{update.timestamp}] {update.location} - "
        #               f"{update.description} ({PackageStatus.Name(update.status)})")
        # except grpc.RpcError as e:
        #     print(f"Error: {e.details()}")

        print("Request:")
        print(f"  Tracking Number: {tracking_number}\n")

        print("Expected Response (streaming):")
        print("  [2024-01-01 10:00:00] Package Created - Package has been created (PENDING)")
        print("  [2024-01-01 14:30:00] Warehouse A - Package picked up (PICKED_UP)")
        print("  [2024-01-01 18:45:00] Distribution Center - In transit (IN_TRANSIT)")
        print("  ...\n")

    def example_cancel_package(self, tracking_number='PKG12345678'):
        """Example 6: Cancel package"""
        print("Example 6: Cancelling package")
        print("-" * 40)

        # In a real implementation:
        # request = CancelPackageRequest(
        #     tracking_number=tracking_number,
        #     reason="Customer requested cancellation"
        # )
        #
        # try:
        #     response = self.stub.CancelPackage(request)
        #     print("Package cancelled successfully!")
        #     print(f"Status: {PackageStatus.Name(response.status)}")
        # except grpc.RpcError as e:
        #     print(f"Error: {e.details()}")

        print("Request:")
        print(f"  Tracking Number: {tracking_number}")
        print("  Reason: Customer requested cancellation\n")

        print("Expected Response:")
        print("  Package with CANCELLED status\n")

    def run_all_examples(self):
        """Run all examples"""
        self.example_create_package()
        self.example_get_package()
        self.example_update_location()
        self.example_list_packages()
        self.example_track_package()
        self.example_cancel_package()

        print("\n" + "=" * 40)
        print("Note: This is a demonstration client showing the API structure.")
        print("To use with a real server:")
        print("1. Generate client stubs from proto files")
        print("2. Uncomment the implementation code")
        print("3. Ensure the gRPC server is running on localhost:9001")

    def close(self):
        """Close the gRPC channel"""
        # In a real implementation:
        # self.channel.close()
        pass


if __name__ == '__main__':
    client = PackageDeliveryClient()
    client.run_all_examples()
    client.close()
