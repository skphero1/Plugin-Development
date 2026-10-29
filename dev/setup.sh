#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
npm ci --no-audit --no-fund
# The container web-server user must be able to read the bind-mounted source.
chmod 755 . assets tests dev
chmod 644 atelier-builder.php assets/* tests/* dev/*
npm test
bash dev/start.sh
