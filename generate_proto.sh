#!/bin/bash

# Script to generate PHP code from proto files

# Create output directory if it doesn't exist
mkdir -p app/Grpc/Generated

# Generate PHP code from proto files
protoc --proto_path=proto \
    --php_out=app/Grpc/Generated \
    --grpc_out=app/Grpc/Generated \
    --plugin=protoc-gen-grpc=/usr/local/bin/grpc_php_plugin \
    proto/package_delivery.proto

echo "Proto files generated successfully!"
echo "Generated files are in app/Grpc/Generated/"
