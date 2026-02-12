.PHONY: help install proto-generate server-start test

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies
	composer install
	cp .env.example .env || true
	php artisan key:generate
	php artisan migrate

proto-generate: ## Generate PHP code from proto files
	@echo "Generating PHP code from proto files..."
	@bash generate_proto.sh

server-start: ## Start gRPC server with RoadRunner
	@echo "Starting gRPC server..."
	./rr serve

server-stop: ## Stop gRPC server
	@echo "Stopping gRPC server..."
	./rr stop

test: ## Run tests
	php artisan test

db-migrate: ## Run database migrations
	php artisan migrate

db-fresh: ## Fresh database with seeders
	php artisan migrate:fresh --seed
