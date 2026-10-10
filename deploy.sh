#!/bin/bash
# Update OneClick Control Center di server (Docker).
# Pemakaian (dari folder proyek):  ./deploy.sh [branch]      contoh: ./deploy.sh main
#
# Langkah: backup database -> git pull -> build ulang image (composer + npm di dalam image)
#          -> restart container (migrate & cache otomatis di entrypoint) -> cek /up.
set -euo pipefail

BRANCH="${1:-$(git rev-parse --abbrev-ref HEAD)}"
BACKUP_DIR="./backups"
COMPOSE="docker compose"

log() { echo -e "\033[0;34m[$(date +'%Y-%m-%d %H:%M:%S')]\033[0m $1"; }
fail() { echo -e "\033[0;31m[GAGAL]\033[0m $1"; exit 1; }

[ -f .env ] || fail "File .env belum ada. Jalankan: cp .env.docker.example .env lalu isi nilainya."

mkdir -p "$BACKUP_DIR"
if $COMPOSE ps --status running mysql | grep -q oneclick_cc_mysql; then
    BACKUP_FILE="$BACKUP_DIR/db_$(date +%Y%m%d_%H%M%S).sql.gz"
    log "Backup database ke $BACKUP_FILE ..."
    $COMPOSE exec -T mysql sh -c 'mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines "$MYSQL_DATABASE"' | gzip > "$BACKUP_FILE" \
        || fail "Backup database gagal; deploy dibatalkan."
fi

log "Menarik kode terbaru dari branch $BRANCH ..."
git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

log "Build image & restart container ..."
docker network inspect edge >/dev/null 2>&1 || docker network create edge   # jaringan bersama reverse proxy (OneClick/infra/edge)
$COMPOSE up -d --build --remove-orphans

log "Menunggu aplikasi siap (migrate & cache berjalan di entrypoint) ..."
for attempt in $(seq 1 30); do
    if $COMPOSE exec -T app wget -qO- http://127.0.0.1/up >/dev/null 2>&1; then
        log "Deploy selesai. Aplikasi sehat."
        $COMPOSE image prune -f >/dev/null 2>&1 || true
        exit 0
    fi
    sleep 5
done

$COMPOSE logs --tail=80 app
fail "Aplikasi belum sehat setelah 150 detik. Lihat log di atas."
