#!/usr/bin/env bash
# ── Phyto E-Commerce Daily Backup ────────────────────────────────────────────
# Backs up each tenant's MySQL database and the provisioning SQLite DB.
# Run via cron daily at 2am:
#   0 2 * * * /opt/phyto-ecommerce-api/scripts/backup.sh >> /var/log/phyto-backup.log 2>&1
# ─────────────────────────────────────────────────────────────────────────────

set -euo pipefail

LOG_PREFIX="[$(date '+%Y-%m-%d %H:%M:%S')] [backup]"
DB_PATH="${DB_PATH:-/opt/phyto-ecommerce-api/data/tenants.db}"
BACKUP_DIR="${BACKUP_DIR:-/opt/phyto-backups}"
RETENTION_DAYS=7
DATE_STAMP=$(date '+%Y-%m-%d')

# B2 / S3-compatible offsite backup (optional — set env vars to enable)
B2_KEY_ID="${B2_KEY_ID:-}"
B2_APP_KEY="${B2_APP_KEY:-}"
B2_BUCKET="${B2_BUCKET_NAME:-phyto-ecommerce-backups}"

mkdir -p "$BACKUP_DIR/mysql" "$BACKUP_DIR/sqlite"

echo "$LOG_PREFIX Starting backup (date: $DATE_STAMP)"

# ── 1. Backup provisioning SQLite DB ─────────────────────────────────────────

SQLITE_BACKUP="$BACKUP_DIR/sqlite/tenants-${DATE_STAMP}.db"
sqlite3 "$DB_PATH" ".backup $SQLITE_BACKUP" \
  && echo "$LOG_PREFIX SQLite backed up to $SQLITE_BACKUP" \
  || echo "$LOG_PREFIX ERROR: SQLite backup failed"

# ── 2. Backup each tenant's MySQL ────────────────────────────────────────────

SLUGS=$(sqlite3 "$DB_PATH" \
  "SELECT slug FROM tenants WHERE status IN ('active','suspended');" 2>/dev/null || echo "")

while IFS= read -r slug; do
  [[ -z "$slug" ]] && continue

  CONTAINER="mysql_${slug}"
  DB_NAME="ps_${slug}"
  OUT_FILE="$BACKUP_DIR/mysql/${slug}-${DATE_STAMP}.sql.gz"

  # Get MySQL password from SQLite
  MYSQL_PASS=$(sqlite3 "$DB_PATH" \
    "SELECT mysql_password FROM tenants WHERE slug='$slug';" 2>/dev/null || echo "")

  if [[ -z "$MYSQL_PASS" ]]; then
    echo "$LOG_PREFIX WARN: No MySQL password found for $slug — skipping"
    continue
  fi

  if ! docker inspect "$CONTAINER" &>/dev/null; then
    echo "$LOG_PREFIX WARN: MySQL container not found for $slug — skipping"
    continue
  fi

  docker exec "$CONTAINER" \
    mysqldump -u root --password="$MYSQL_PASS" \
    --single-transaction --quick --lock-tables=false \
    "$DB_NAME" 2>/dev/null \
    | gzip > "$OUT_FILE" \
    && echo "$LOG_PREFIX MySQL backup: $slug → $OUT_FILE" \
    || echo "$LOG_PREFIX ERROR: MySQL backup failed for $slug"

done <<< "$SLUGS"

# ── 3. Prune old local backups ────────────────────────────────────────────────

find "$BACKUP_DIR/mysql"  -name "*.sql.gz" -mtime "+${RETENTION_DAYS}" -delete
find "$BACKUP_DIR/sqlite" -name "*.db"     -mtime "+${RETENTION_DAYS}" -delete
echo "$LOG_PREFIX Pruned backups older than ${RETENTION_DAYS} days"

# ── 4. Offsite backup to Backblaze B2 ────────────────────────────────────────

if [[ -n "$B2_KEY_ID" && -n "$B2_APP_KEY" ]]; then
  if command -v b2 &>/dev/null; then
    b2 authorize-account "$B2_KEY_ID" "$B2_APP_KEY" 2>/dev/null

    # Sync today's backups
    b2 sync "$BACKUP_DIR/mysql"  "b2://${B2_BUCKET}/mysql/"  --newer-file-mode replace \
      && echo "$LOG_PREFIX B2 sync: mysql backups uploaded" \
      || echo "$LOG_PREFIX WARN: B2 sync failed for mysql"

    b2 sync "$BACKUP_DIR/sqlite" "b2://${B2_BUCKET}/sqlite/" --newer-file-mode replace \
      && echo "$LOG_PREFIX B2 sync: sqlite backups uploaded" \
      || echo "$LOG_PREFIX WARN: B2 sync failed for sqlite"
  else
    echo "$LOG_PREFIX WARN: B2 CLI not installed — skipping offsite backup"
    echo "$LOG_PREFIX       Install: pip install b2"
  fi
else
  echo "$LOG_PREFIX INFO: B2 credentials not set — skipping offsite backup"
fi

echo "$LOG_PREFIX Backup complete"
