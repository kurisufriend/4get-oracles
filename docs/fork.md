# Oracle-preserving fork

This fork restores the calculator, text encoder/hasher, numeric base converter,
and time oracle removed by upstream commit
`6602df4fa9cd8aa4a1e1065ee3ef8344830915bf`. The initial restoration is the exact
reverse of that commit, including its `web.php` integration.

## Upstream updates

The **Sync upstream** GitHub Actions workflow checks every five minutes (at
minutes 02, 07, 12, and so on) and can also be run manually. GitHub may delay
scheduled runs; this is not a guaranteed five-minute delivery time. It merges `master` from
https://git.lolcat.ca/lolcat/4get.git into this repository's `master`, runs PHP
syntax and oracle smoke checks, and pushes only if all steps succeed. No custom
secrets are needed; the workflow uses the repository's `GITHUB_TOKEN` with
`contents: write`. It also grants `packages: write` to the reusable container
publishing job. Repository policies must permit Actions to push to `master`.
Unchanged polls do not push or build a container.

The oracle restoration is retained as a fork change by Git's three-way merge;
it does not need to be reapplied after every upstream update. Fork commits and
history are preserved, and the workflow never force-pushes. Conflicts, failed
checks, or a concurrent push stop publication. Inspect failed runs in Actions
and resolve conflicts manually. Tests cover representative oracle queries and
the presence of search integration, not live search providers or every legacy
oracle edge case.

GitHub can disable scheduled workflows in inactive public repositories after
60 days; re-enable the workflow in Actions if necessary.

## Local checks and sync

With PHP CLI, Bash, Git, and GNU `timeout` installed:

```sh
bash scripts/check-oracles.sh
bash scripts/sync-upstream.sh
```

Local sync requires a clean working tree and a Git commit identity. It merges
and checks but never pushes. A failed validation leaves the local merge available
for inspection; resolve it before publishing. GitHub runs the same checks on
pushes and pull requests, and explicitly before publishing scheduled merges
because pushes made with `GITHUB_TOKEN` do not trigger another push workflow.

GitHub references: [scheduled workflows](https://docs.github.com/en/actions/reference/workflows-and-actions/events-that-trigger-workflows#schedule) and [token-triggered events](https://docs.github.com/en/actions/how-tos/write-workflows/choose-when-workflows-run/trigger-a-workflow#triggering-a-workflow-from-a-workflow).

## Container image

The **Publish container** workflow builds and tests the Docker image, then pushes
`ghcr.io/kurisufriend/4get-oracles:latest` and `sha-<full-commit-SHA>` tags.
It runs on pushes to `master`, after successful upstream syncs that changed
the branch, and manually. Sync calls the reusable publishing workflow directly
because a push made with `GITHUB_TOKEN` does not trigger a push workflow.
The image currently targets `linux/amd64`, matching the curl-impersonate base.

```sh
docker pull ghcr.io/kurisufriend/4get-oracles:latest
docker compose up -d
```

Publishing uses the repository's `GITHUB_TOKEN` with `packages: write`. GHCR
packages initially default to private; enable public visibility in the package
settings if anonymous pulls are needed.
