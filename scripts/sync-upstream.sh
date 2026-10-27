#!/usr/bin/env bash
set -euo pipefail
cd "$(git rev-parse --show-toplevel)"

if [[ -n "$(git status --porcelain)" ]]; then
    echo 'Sync requires a clean working tree.' >&2
    exit 1
fi

# Fetch only the upstream branch, preserving the fork's commits and history.
git fetch --no-tags https://git.lolcat.ca/lolcat/4get.git master
if ! git merge --no-edit FETCH_HEAD; then
    git merge --abort
    echo 'Upstream conflicts with the fork. Resolve manually; nothing was pushed.' >&2
    exit 1
fi

# A failed check leaves the merge local for inspection, but the workflow cannot push.
bash scripts/check-oracles.sh
