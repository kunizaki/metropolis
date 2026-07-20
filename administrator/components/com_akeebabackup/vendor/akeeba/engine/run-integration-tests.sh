#!/usr/bin/env bash
# =============================================================================
# Akeeba Engine — MySQL/MariaDB integration test runner
#
# Spins up ephemeral Docker containers for each configured server version,
# runs the PHPUnit integration suite against each one, then tears them down.
#
# Usage:
#   ./run-integration-tests.sh               # test all configured images
#   ./run-integration-tests.sh mysql:8.0     # test one specific image
#
# Override the database password or name via environment variables:
#   DB_PASSWORD=secret DB_NAME=mydb ./run-integration-tests.sh
#
# Requirements: Docker, PHP CLI (with mysqli and PDO/mysql extensions)
# =============================================================================

set -euo pipefail

# ---------------------------------------------------------------------------
# Configuration — edit here to add or remove server versions
# ---------------------------------------------------------------------------

DEFAULT_IMAGES=(
	"mysql:8.0"
	"mysql:8.4"
	"mariadb:11.4"
	"mariadb:12.3"
)

# Host port that Docker will map to the container's 3306.
# Change this if 13306 is already occupied on your machine.
DB_HOST_PORT="${DB_HOST_PORT:-13306}"

# Database credentials used inside the container.
DB_PASSWORD="${DB_PASSWORD:-akeebatest}"
DB_NAME="${DB_NAME:-akeebatest}"

# How long (in seconds × 2) to wait for the server to become ready.
MAX_WAIT_TRIES=60

# ---------------------------------------------------------------------------
# Resolve which images to test
# ---------------------------------------------------------------------------

if [ "${1:-}" != "" ]; then
	IMAGES=("$1")
else
	IMAGES=("${DEFAULT_IMAGES[@]}")
fi

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

CONTAINER_NAME="akeebaengine_integration_$$"
FAILED=0

cleanup_container() {
	docker stop "$CONTAINER_NAME" >/dev/null 2>&1 || true
	docker rm   "$CONTAINER_NAME" >/dev/null 2>&1 || true
}

wait_for_db() {
	local tries=0

	echo "  Waiting for database server to accept connections..."

	until docker exec "$CONTAINER_NAME" mysql \
			--user=root \
			"--password=${DB_PASSWORD}" \
			--execute="SELECT 1" \
			"${DB_NAME}" >/dev/null 2>&1
	do
		tries=$((tries + 1))
		if [ $tries -ge $MAX_WAIT_TRIES ]; then
			echo "  Timed out after $((MAX_WAIT_TRIES * 2)) seconds."
			return 1
		fi
		sleep 2
	done

	echo "  Database is ready."
	return 0
}

run_tests_against() {
	local image="$1"

	echo ""
	echo "=========================================="
	echo "  Image : $image"
	echo "=========================================="

	# Remove any leftover container with the same name.
	cleanup_container

	# Start the container.  Both MYSQL_* and MARIADB_* env vars are supplied
	# so the image works regardless of which set it recognises.
	docker run --detach \
		--name "$CONTAINER_NAME" \
		--env "MYSQL_ROOT_PASSWORD=${DB_PASSWORD}" \
		--env "MARIADB_ROOT_PASSWORD=${DB_PASSWORD}" \
		--env "MYSQL_DATABASE=${DB_NAME}" \
		--env "MARIADB_DATABASE=${DB_NAME}" \
		--publish "127.0.0.1:${DB_HOST_PORT}:3306" \
		"$image" >/dev/null

	if ! wait_for_db; then
		echo "  FAILED: server did not become ready ($image)"
		cleanup_container
		return 1
	fi

	# Run the PHPUnit integration suite with the connection details exported.
	if INTEGRATION_DB_HOST=127.0.0.1 \
		INTEGRATION_DB_PORT="$DB_HOST_PORT" \
		INTEGRATION_DB_USER=root \
		INTEGRATION_DB_PASSWORD="$DB_PASSWORD" \
		INTEGRATION_DB_NAME="$DB_NAME" \
		vendor/bin/phpunit --configuration phpunit.integration.xml; then
		echo ""
		echo "  PASSED: $image"
	else
		echo ""
		echo "  FAILED: $image (test failures above)"
		cleanup_container
		return 1
	fi

	cleanup_container
	return 0
}

# ---------------------------------------------------------------------------
# Main loop
# ---------------------------------------------------------------------------

for IMAGE in "${IMAGES[@]}"; do
	if ! run_tests_against "$IMAGE"; then
		FAILED=$((FAILED + 1))
	fi
done

# ---------------------------------------------------------------------------
# Summary
# ---------------------------------------------------------------------------

echo ""
echo "=========================================="
TOTAL="${#IMAGES[@]}"
PASSED=$((TOTAL - FAILED))
echo "  Results: $PASSED/$TOTAL image(s) passed"
echo "=========================================="

if [ $FAILED -ne 0 ]; then
	exit 1
fi
