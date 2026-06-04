#!/usr/bin/env bash
set -euo pipefail

# ─── Configuration ───────────────────────────────────────────────────────────
DEPLOY_PATH="${DEPLOY_PATH:-/var/www/plazaos}"
RELEASE_DIR="${DEPLOY_PATH}/releases"
SHARED_DIR="${DEPLOY_PATH}/shared"
CURRENT_DIR="${DEPLOY_PATH}/current"
RELEASE="$(date +%Y%m%d%H%M%S)"
RELEASE_PATH="${RELEASE_DIR}/${RELEASE}"

REPO_URL="${REPO_URL:-git@github.com:raviensewpal/plazaos.git}"
BRANCH="${BRANCH:-production}"

COMPOSER="${COMPOSER:-composer}"
NPM="${NPM:-npm}"
PHP="${PHP:-php}"

# ─── Helpers ─────────────────────────────────────────────────────────────────
info()  { echo -e "\033[1;34m•\033[0m $1"; }
ok()    { echo -e "\033[1;32m✓\033[0m $1"; }
fail()  { echo -e "\033[1;31m✗\033[0m $1"; exit 1; }

# ─── Prerequisites ───────────────────────────────────────────────────────────
for cmd in git "$COMPOSER" "$NPM" "$PHP"; do
    command -v "$cmd" >/dev/null 2>&1 || fail "Missing: $cmd"
done

# ─── Prepare directories ─────────────────────────────────────────────────────
mkdir -p "$RELEASE_DIR" "$SHARED_DIR"

# ─── Fetch source ────────────────────────────────────────────────────────────
if [ ! -d "${RELEASE_DIR}/.git" ]; then
    info "Cloning repository..."
    git clone --depth=1 --branch "$BRANCH" "$REPO_URL" "$RELEASE_PATH"
else
    info "Reusing local git repo..."
    git -C "$RELEASE_DIR" fetch --depth=1 origin "$BRANCH"
    git -C "$RELEASE_DIR" archive --format=tar origin/"$BRANCH" | tar xf - -C "$RELEASE_PATH"
fi

cd "$RELEASE_PATH"

ok "Source fetched (release: $RELEASE)"

# ─── Copy .env ───────────────────────────────────────────────────────────────
if [ -f "${SHARED_DIR}/.env" ]; then
    cp "${SHARED_DIR}/.env" .env
    ok "Copied .env from shared"
else
    cp .env.example .env
    info ".env copied from .env.example — edit it!"
fi

# ─── Install dependencies ────────────────────────────────────────────────────
info "Installing Composer dependencies..."
$COMPOSER install --no-dev --optimize-autoloader --no-interaction --quiet
ok "Composer dependencies installed"

info "Installing Node dependencies..."
$NPM ci --omit=dev --quiet 2>/dev/null || $NPM ci --quiet
ok "Node dependencies installed"

# ─── Build assets ────────────────────────────────────────────────────────────
info "Building frontend assets..."
$NPM run build --quiet 2>/dev/null || $NPM run build
ok "Assets built"

# ─── Link shared directories ─────────────────────────────────────────────────
for dir in storage; do
    if [ -d "${SHARED_DIR}/${dir}" ]; then
        rm -rf "${RELEASE_PATH}/${dir}"
        ln -sfn "${SHARED_DIR}/${dir}" "${RELEASE_PATH}/${dir}"
    fi
done
ok "Shared directories linked"

# ─── Storage setup ───────────────────────────────────────────────────────────
$PHP artisan storage:link --quiet --force 2>/dev/null || true
ok "Storage link created"

# ─── Migrate ─────────────────────────────────────────────────────────────────
info "Running migrations..."
$PHP artisan migrate --force --quiet
ok "Migrations up to date"

# ─── Optimize ────────────────────────────────────────────────────────────────
info "Caching config, routes, and views..."
$PHP artisan config:cache --quiet
$PHP artisan route:cache --quiet
$PHP artisan view:cache --quiet
$PHP artisan event:cache --quiet
ok "Optimizations cached"

# ─── Symlink current release ─────────────────────────────────────────────────
ln -sfn "$RELEASE_PATH" "$CURRENT_DIR"
ok "Symlinked current → ${RELEASE}"

# ─── Restart services ────────────────────────────────────────────────────────
if command -v supervisorctl &>/dev/null; then
    supervisorctl restart plazaos-queue:* 2>/dev/null || true
    ok "Queue worker restarted"
fi

if command -v systemctl &>/dev/null; then
    systemctl reload php8.4-fpm 2>/dev/null || systemctl reload php8.3-fpm 2>/dev/null || true
    ok "PHP-FPM reloaded"
fi

# ─── Cleanup old releases ────────────────────────────────────────────────────
KEEP=5
ls -1t "$RELEASE_DIR" | tail -n +$((KEEP + 1)) | while read -r old; do
    rm -rf "${RELEASE_DIR:?}/${old}"
done
ok "Cleaned up releases (keeping last $KEEP)"

# ─── Verify ──────────────────────────────────────────────────────────────────
if [ -f "${CURRENT_DIR}/public/index.php" ]; then
    ok "Deploy complete: ${RELEASE}"
else
    fail "Deploy failed — index.php not found in release"
fi
