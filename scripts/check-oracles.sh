#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
for file in oracles/base.php oracles/calc.php oracles/encoder.php oracles/numerics.php oracles/time.php web.php; do
    php -l "$file"
done
timeout 20s php tests/oracles.php
