#!/usr/bin/env bash
# ── Phyto E-Commerce Tenant Healthcheck ──────────────────────────────────────
# Run via cron every 5 minutes:
#   */5 * * * * /opt/phyto-ecommerce-api/scripts/healthcheck.sh >> /var/log/phyto-healthcheck.log 2>&1
# ─────────────────────────────────────────────────────────────────────────────

set -euo pipefail

LOG_PREFIX="[$(date '+%Y-%m-%d %H:%M:%S')] [healthcheck]"
DB_PATH="${DB_PATH:-/opt/phyto-ecommerce-api/data/tenants.db}"
UNHEALTHY_THRESHOLD=2   # Restart after N consecutive failures

echo "$LOG_PREFIX Starting healthcheck"

# Fetch all active tenant slugs from SQLite
if ! command -v sqlite3 &>/dev/null; then
  echo "$LOG_PREFIX ERROR: sqlite3 not installed"
  exit 1
fi

SLUGS=$(sqlite3 "$DB_PATH" \
  "SELECT slug FROM tenants WHERE status = 'active';" 2>/dev/null || echo "")

if [[ -z "$SLUGS" ]]; then
  echo "$LOG_PREFIX No active tenants to check"
  exit 0
fi

while IFS= read -r slug; do
  [[ -z "$slug" ]] && continue

  CONTAINER="ps_${slug}"

  # Check if container exists
  if ! docker inspect "$CONTAINER" &>/dev/null; then
    echo "$LOG_PREFIX MISSING container for tenant: $slug"
    continue
  fi

  # Get container health status
  HEALTH=$(docker inspect --format='{{.State.Health.Status}}' "$CONTAINER" 2>/dev/null || echo "unknown")
  STATE=$(docker inspect --format='{{.State.Status}}'        "$CONTAINER" 2>/dev/null || echo "unknown")

  if [[ "$STATE" == "running" && "$HEALTH" == "healthy" ]]; then
    echo "$LOG_PREFIX OK: $slug ($CONTAINER)"

  elif [[ "$STATE" == "paused" ]]; then
    echo "$LOG_PREFIX PAUSED (suspended): $slug — skipping"

  elif [[ "$STATE" == "running" && "$HEALTH" == "unhealthy" ]]; then
    echo "$LOG_PREFIX UNHEALTHY: $slug — restarting container"
    docker restart "$CONTAINER" || echo "$LOG_PREFIX ERROR: Failed to restart $CONTAINER"

  elif [[ "$STATE" != "running" ]]; then
    echo "$LOG_PREFIX NOT RUNNING: $slug (state=$STATE) — starting"

    COMPOSE_FILE="/opt/phyto-tenants/docker-compose.${slug}.yml"
    if [[ -f "$COMPOSE_FILE" ]]; then
      docker compose -f "$COMPOSE_FILE" up -d \
        && echo "$LOG_PREFIX Started: $slug" \
        || echo "$LOG_PREFIX ERROR: Failed to start $slug"
    else
      echo "$LOG_PREFIX ERROR: No compose file for $slug at $COMPOSE_FILE"
    fi

  else
    echo "$LOG_PREFIX CHECKING: $slug (state=$STATE health=$HEALTH)"
  fi

done <<< "$SLUGS"

echo "$LOG_PREFIX Healthcheck complete"
