#!/usr/bin/env bash
#
# Deploy SureBasketball from this machine to the everule server.
# Target: ~/surebasketball.com (document root for surebasketball.com)
#
# Usage:
#   ./deploy.sh                     # sync + remote setup (deploy to livescorestips.com)
#   ./deploy.sh --target livescores # deploy to livescorestips.com instead
#   ./deploy.sh --dry-run           # show what would be transferred
#   ./deploy.sh --sync-only         # rsync only, skip composer/artisan
#   ./deploy.sh --prune             # delete remote files not present locally (careful)
#   ./deploy.sh --push-env          # upload .env once (production tweaks; never overwrites existing)
#
set -euo pipefail

# --- configuration ---
SSH_HOST="${DEPLOY_SSH_HOST:-everule}"
REMOTE_USER="${DEPLOY_REMOTE_USER:-everxgzn}"
REMOTE_PATH="${DEPLOY_REMOTE_PATH:-livescorestips.com}"
PHP_BIN="${DEPLOY_PHP_BIN:-/usr/local/bin/php}"
COMPOSER_BIN="${DEPLOY_COMPOSER_BIN:-/home/everxgzn/composer}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOCAL_PATH="${DEPLOY_LOCAL_PATH:-$SCRIPT_DIR}"

DRY_RUN=false
SYNC_ONLY=false
PRUNE=false
PUSH_ENV=false

for arg in "$@"; do
    case "$arg" in
        --dry-run) DRY_RUN=true ;;
        --sync-only) SYNC_ONLY=true ;;
        --prune) PRUNE=true ;;
        --push-env) PUSH_ENV=true ;;
        --target=*) TARGET="${arg#*=}" ;;
        --target)
            echo "Use --target=<name> (e.g. --target=livescores)" >&2
            exit 1
            ;;
        -h|--help)
            sed -n '2,13p' "$0"
            exit 0
            ;;
        *)
            echo "Unknown option: $arg" >&2
            exit 1
            ;;
    esac
done

# Map --target shorthand to REMOTE_PATH (unless DEPLOY_REMOTE_PATH is set)
if [ -n "${TARGET:-}" ] && [ -z "${DEPLOY_REMOTE_PATH:-}" ]; then
    case "$TARGET" in
        livescores|new) REMOTE_PATH="livescorestips.com" ;;
        tipskick|old)   REMOTE_PATH="tipskickapi.shop" ;;
        *)
            echo "Unknown target: $TARGET (valid: tipskick, livescores)" >&2
            exit 1
            ;;
    esac
fi

REMOTE="${REMOTE_USER}@${SSH_HOST}"
REMOTE_DIR="~/${REMOTE_PATH}"

RSYNC_OPTS=(-avz --human-readable)
EXCLUDES=(
    --exclude='.env'
    --exclude='.env.*'
    --exclude='.git/'
    --exclude='.gitignore'
    --exclude='.DS_Store'
    --exclude='.cursor/'
    --exclude='node_modules/'
    --exclude='vendor/'
    --exclude='bootstrap/cache/'
    --exclude='storage/logs/*'
    --exclude='storage/framework/cache/data/*'
    --exclude='storage/framework/sessions/*'
    --exclude='storage/framework/views/*'
    --exclude='error_log'
    --exclude='deploy.sh'
    --exclude='hot'
    --exclude='public/articles'
    --exclude='template/'
)

if $DRY_RUN; then
    RSYNC_OPTS+=(-n --itemize-changes)
fi

if $PRUNE; then
    RSYNC_OPTS+=(--delete)
fi

echo "==> Deploying LivescoresTips"
echo "    Local:  ${LOCAL_PATH}"
echo "    Remote: ${REMOTE}:${REMOTE_DIR}"
echo ""

push_env_to_server() {
    local env_file="${LOCAL_PATH}/.env"
    if [[ ! -f "$env_file" ]]; then
        echo "ERROR: ${env_file} not found." >&2
        exit 1
    fi

    local tmp
    tmp="$(mktemp)"
    # Production-safe tweaks for cPanel (never copy local DB_SOCKET)
    grep -v '^DB_SOCKET=' "$env_file" | \
        sed -e 's/^APP_ENV=.*/APP_ENV=production/' \
            -e 's/^APP_DEBUG=.*/APP_DEBUG=false/' \
            -e 's|^APP_URL=.*|APP_URL=https://livescorestips.com|' \
            -e 's/^LOG_LEVEL=.*/LOG_LEVEL=error/' \
        > "$tmp"

    if ssh "${REMOTE}" "test -f \$HOME/${REMOTE_PATH}/.env"; then
        echo "==> Server .env already exists — not overwriting."
        rm -f "$tmp"
        return 0
    fi

    echo "==> Uploading .env to server (first time only)..."
    scp -q "$tmp" "${REMOTE}:${REMOTE_DIR}/.env"
    rm -f "$tmp"
    ssh "${REMOTE}" "chmod 600 \$HOME/${REMOTE_PATH}/.env"
}

if $PUSH_ENV; then
    push_env_to_server
    if $SYNC_ONLY || $DRY_RUN; then
        exit 0
    fi
fi

# Optional: build frontend assets locally before upload
if [[ -f "${LOCAL_PATH}/package.json" ]] && [[ -f "${LOCAL_PATH}/vite.config.js" ]] && ! $SYNC_ONLY && ! $DRY_RUN; then
    if command -v npm >/dev/null 2>&1; then
        echo "==> Building assets (npm run build)..."
        (cd "${LOCAL_PATH}" && npm run build --if-present) || echo "    (npm build skipped or failed — continuing)"
    fi
fi

echo "==> Syncing files (rsync)..."
rsync "${RSYNC_OPTS[@]}" "${EXCLUDES[@]}" \
    "${LOCAL_PATH}/" \
    "${REMOTE}:${REMOTE_DIR}/"

if $DRY_RUN || $SYNC_ONLY; then
    echo ""
    echo "Done (no remote setup)."
    exit 0
fi

echo ""
echo "==> Running remote setup..."

ssh "${REMOTE}" bash -s "${REMOTE_PATH}" "${PHP_BIN}" "${COMPOSER_BIN}" <<'REMOTE_SETUP'
set -euo pipefail

REMOTE_SUBDIR="$1"
PHP_BIN="$2"
COMPOSER_BIN="$3"
cd "${HOME}/${REMOTE_SUBDIR}"

# Laravel .htaccess for cPanel (document root = app root)
if [[ ! -f .htaccess ]]; then
    cat > .htaccess <<'HTACCESS'
# Block access to sensitive files
<FilesMatch "^\.env|^\.git|^\.vscode|composer\.json|composer\.lock|package\.json|package-lock\.json">
    Order allow,deny
    Deny from all
</FilesMatch>

<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    RewriteCond %{REQUEST_URI} ^/storage/(.*)$ [NC]
    RewriteCond %{REQUEST_URI} !^/storage/app/public/ [NC]
    RewriteRule ^storage/(.*)$ storage/app/public/$1 [L]

    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Force admin routes that conflict with physical directories to Laravel
    # Only rewrite if the file doesn't actually exist (so uploaded images are served directly)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(articles|scripts|site-links)(/.*)?$ index.php [L]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTACCESS
    echo "Created .htaccess"
fi

# Writable dirs
mkdir -p storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

if [[ ! -f .env ]]; then
    echo "ERROR: .env missing on server. Create it in ~/surebasketball.com/.env before continuing." >&2
    exit 1
fi

# Composer
if [[ -f composer.json ]]; then
    "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction
fi

# App key (only if missing)
if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    "$PHP_BIN" artisan key:generate --force
fi

# Storage link
"$PHP_BIN" artisan storage:link --force 2>/dev/null || true

# Database
"$PHP_BIN" artisan migrate --force --no-interaction

# Cache for production
"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan view:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache

# View cache can fail if a Blade component is missing; site still runs uncached
if ! "$PHP_BIN" artisan view:cache; then
    echo "WARNING: view:cache failed — using uncached views (fix missing Blade components)."
    "$PHP_BIN" artisan view:clear
fi

echo ""
echo "Remote setup complete."
REMOTE_SETUP

echo ""
echo "==> Deploy finished."
echo "    Site: https://livescorestips.com"
echo "    Path: ${REMOTE_DIR}"
echo ""
echo "Tip: server .env is never overwritten by deploy.sh"
