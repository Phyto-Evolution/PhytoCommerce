#!/bin/bash
# PhytoCommerce VPS Setup Script
# Run this ONCE on the VPS (ubuntu@51.83.192.49) to:
#   1. Install SSH public key for passwordless Claude Code access
#   2. Clone/update the PhytoCommerce repo
#   3. Verify Claude Code is installed
#
# Usage (from your local machine):
#   sshpass -p 'your-password' ssh ubuntu@51.83.192.49 'bash -s' < docs/vps-setup.sh

set -e

REPO_URL="https://github.com/Phyto-Evolution/PhytoCommerce.git"
REPO_BRANCH="claude/phytocommerce-module-dev-HGpZM"
REPO_DIR="$HOME/PhytoCommerce"

echo "=== PhytoCommerce VPS Setup ==="
echo "Host: $(hostname) | $(date -u)"

# ── 1. Install SSH public key ──────────────────────────────────────────────────
mkdir -p ~/.ssh && chmod 700 ~/.ssh
PUBKEY="ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAINcW4ihAyoqDaExb6ZXA3876IueXpNUy+N6I6IEDC8uW phytocommerce-claude-code"
if ! grep -qF "$PUBKEY" ~/.ssh/authorized_keys 2>/dev/null; then
    echo "$PUBKEY" >> ~/.ssh/authorized_keys
    chmod 600 ~/.ssh/authorized_keys
    echo "[OK] SSH public key installed"
else
    echo "[SKIP] SSH public key already present"
fi

# ── 2. Clone or update repo ────────────────────────────────────────────────────
if [ -d "$REPO_DIR/.git" ]; then
    echo "[INFO] Repo exists — pulling latest..."
    git -C "$REPO_DIR" fetch origin "$REPO_BRANCH"
    git -C "$REPO_DIR" checkout "$REPO_BRANCH"
    git -C "$REPO_DIR" pull origin "$REPO_BRANCH"
else
    echo "[INFO] Cloning repo..."
    git clone --branch "$REPO_BRANCH" "$REPO_URL" "$REPO_DIR"
fi
echo "[OK] Repo at: $REPO_DIR ($(git -C "$REPO_DIR" log --oneline -1))"

# ── 3. Check Claude Code ───────────────────────────────────────────────────────
if command -v claude &>/dev/null; then
    echo "[OK] Claude Code: $(claude --version 2>/dev/null || echo 'installed')"
else
    echo "[WARN] Claude Code not found in PATH. Install it:"
    echo "       npm install -g @anthropic-ai/claude-code"
fi

# ── 4. Check PHP / PrestaShop presence ────────────────────────────────────────
echo ""
echo "=== Environment Summary ==="
php --version 2>/dev/null | head -1 || echo "PHP: not found"
ls /var/www/ 2>/dev/null && echo "Web root: /var/www/" || echo "Web root: not found at /var/www"
ls /var/www/html/ 2>/dev/null | head -5 || true

echo ""
echo "=== Setup Complete ==="
echo "Repo:   $REPO_DIR"
echo "Branch: $REPO_BRANCH"
