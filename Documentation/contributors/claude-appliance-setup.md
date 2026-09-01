# Claude Code Multi-Agent LXC Appliance Setup

## Architecture

![Claude Code multi-agent LXC appliance architecture](../images/claude-appliance-architecture.svg)

---

## Overview

A jailed Ubuntu LXC container running on the host Linux machine, connected to the host's git directory via bind mount (mirrored path). The container uses LXC's default NAT bridge (`lxcbr0`) rather than a separate LAN/host bridge such as `br0`, with a static IP inside the container and a single `/etc/hosts` entry on the host for name resolution.

**Key design decisions:**
- PHP, Composer, and Node for OpenEMR work are **not** installed in the LXC — all PHP/JS/CSS work runs inside the demo stacks via `openemr-cmd` commands (`docker exec`). Node is installed only to run the agent CLI itself (Claude Code).
- LXC provides the jail — agents cannot reach host Docker, host filesystem outside the bind mount, or host localhost services
- **NAT networking** (not bridged) — LAN hosts cannot initiate connections *to* the container; the container is invisible from outside the host. The container can still reach the LAN *outbound* once forwarding is enabled — see threat model below.
- Static IP inside the container + `/etc/hosts` on the host gives a stable `claude-appliance.local` name — no mDNS or Avahi needed
- Works on any connection including mobile hotspot — no dependency on LAN or router config
- `openemr-cmd` handles all worktree lifecycle, port offset management, and Docker stack orchestration

**Containment stack:**
- **LXC** — jails the filesystem, processes, and kernel namespaces
- **NAT networking** — jails inbound network access; container is not reachable from the LAN
- **No OpenEMR dev tooling on appliance** — PHP, Composer, and project Node deps live only inside demo stacks
- **Bind-mounted git directory** — the only intentional read-write bridge to the host

---

## Threat model

Before changing anything below, understand what this appliance is and is not:

- **The container is the trust boundary, not the user inside it.** The agent has effectively full privilege inside the container (root via passwordless sudo, full capabilities for nested Docker, `--dangerously-skip-permissions` on Claude Code). That is intentional — the design treats the *whole appliance* as already-compromised and relies on LXC + NAT to contain blast radius.
- **What is protected:** the host filesystem outside `<git-dir>`, the host Docker daemon, host localhost services, your LAN's inbound surface, and any GitHub/SSH credentials that live on the host but not in the container.
- **What is *not* protected:** anything reachable from the bind-mounted `<git-dir>`, anything the container can dial outbound (LAN included, unless you add egress firewall rules), and the contents of any tokens/keys you place inside the container (the GitHub PAT, the deploy key, the optional GPG key).
- **Posture:** treat the appliance as disposable. Snapshots are the rollback path. Do not put credentials in the container that you would not be willing to revoke. Use a separate GitHub PAT, a per-repo deploy key, and (if you sign) a separate signing key — all documented below.
- **If you want stricter outbound isolation,** add explicit host firewall rules to block container → LAN traffic (e.g. `iptables` rules dropping `lxcbr0` traffic destined for RFC1918 ranges other than the loopback uplink). The default config below does *not* do this.

---

## Step 1 — Configure ufw to allow LXC bridge forwarding

LXC container networking requires packet forwarding through the `lxcbr0` bridge. By default ufw blocks this. Prefer **scoped** route rules over flipping the global default forwarding policy — this keeps `DEFAULT_FORWARD_POLICY="DROP"` for every other interface (VPN tunnels, additional bridges, etc.) and only opens the path the container actually needs.

```bash
# Allow ufw-managed traffic to/from the lxcbr0 bridge itself
sudo ufw allow in on lxcbr0
sudo ufw allow out on lxcbr0

# Allow routed/NAT forwarding for traffic to/from the lxcbr0 subnet.
# We deliberately do not pin the *other* interface so this works regardless
# of which uplink is active (home wifi, ethernet dock, phone tether, mobile
# hotspot, etc.) — the design goal is "works on any connection."
sudo ufw route allow in on lxcbr0 from 10.0.3.0/24
sudo ufw route allow out on lxcbr0 to 10.0.3.0/24

sudo ufw reload
```

> **Why not `DEFAULT_FORWARD_POLICY="ACCEPT"`?** Flipping that flag changes forwarding behavior for *every* interface pair on the host, not just `lxcbr0`. On a multi-interface host (VPN, Docker bridges, additional LXC networks) it can permit unintended traffic paths. The `ufw route` rules above are still meaningfully narrower — they only allow forwarding when one side of the path is `lxcbr0` and the source/destination is the lxcbr0 subnet — but they do not pin the uplink, so they keep working when you switch networks.
>
> **If you want stricter scoping** at the cost of network portability, replace the two `ufw route` lines above with one pair per uplink you use, e.g.:
>
> ```bash
> sudo ufw route allow in on lxcbr0 out on <uplink_if> from 10.0.3.0/24
> sudo ufw route allow in on <uplink_if> out on lxcbr0 to 10.0.3.0/24
> ```
>
> You will then need to re-run those commands (or maintain one pair per interface) whenever you switch between wifi, ethernet, and tethered uplinks.
>
> **If your distro or ufw version does not support `ufw route`** and you must use the global policy switch, document it as a host-wide change and review your other firewall rules first.

---

## Step 2 — Install LXC on the host

```bash
sudo apt update
sudo apt install lxc lxc-utils lxc-templates
```

---

## Step 3 — Create the Ubuntu 24.04 container

```bash
sudo lxc-create -n claude-appliance -t download -- \
  --dist ubuntu --release noble --arch amd64
```

---

## Step 4 — Configure the container

`lxc-create` already generates `/var/lib/lxc/claude-appliance/config` with the hostname, architecture, rootfs path, and NAT networking (including a real MAC address). Append the following block to the end of that existing file. Replace `<git-dir>` with your actual git base directory path (e.g. `/home/<your-username>/git`); the target (second path) must be the same path without the leading `/`.

```bash
sudo tee -a /var/lib/lxc/claude-appliance/config << 'EOF'
# AppArmor: unconfined is currently required for nested Docker.
# See the explanation below for why, and the threat-model section for
# what this posture does and does not protect.
lxc.apparmor.profile = unconfined
# Git directory bind mount (mirror host path exactly)
lxc.mount.entry = <git-dir> <git-dir-no-leading-slash> none bind,create=dir 0 0
EOF
```

For example, if your git base is `/home/alice/git`:

```text
lxc.mount.entry = /home/alice/git home/alice/git none bind,create=dir 0 0
```

> **What this configuration deliberately leaves out.** Older guides for nested Docker in LXC commonly add two more lines: `lxc.cap.drop =` (empty — "drop no capabilities") and `lxc.cgroup2.devices.allow = a` ("allow access to every device node"). Both are over-permissive defaults that LXC does not need — Docker runs fine with the default LXC capability set and the default cgroup device controller. What those lines *do* grant is the easiest container-to-host escape paths: loading kernel modules (`cap_sys_module`), raw block-device I/O (`cap_sys_rawio`), and direct opens of `/dev/sda`-style nodes for "mount the host's disk" tricks. We omit them.

> **Why `unconfined` rather than the `generated` AppArmor profile?** The `generated` profile (with or without `lxc.apparmor.allow_nesting = 1`) blocks `runc` from writing per-container sysctls into `/proc/sys/net/...`, which prevents Docker 26+ from starting any container. `docker run --rm hello-world` fails with `open sysctl net.ipv4.ip_unprivileged_port_start file: reopen fd 8: permission denied`. Until either the LXC profile or Docker's reliance on those sysctls changes upstream, `unconfined` is the working configuration on Ubuntu 24 hosts. This means the LXC stays a *privileged* container — kernel exploits inside it land as host root rather than an unprivileged user. The threat-model section above frames the rest of the posture; tightening this further (unprivileged LXC with idmap mounts) is a future direction, not what is documented here.

> **Portability across Linux distributions.** The configuration above is specific to AppArmor-based hosts (Ubuntu, Debian, openSUSE) on cgroup v2. On other systems:
> - **SELinux hosts (Fedora, RHEL, Rocky, Alma):** the equivalent control is `lxc.selinux.context` — start with `unconfined_t` for permissive operation, or build a project-specific policy. Nested Docker on SELinux often also needs SELinux relaxed inside the container itself (`SELINUX=permissive` in `/etc/selinux/config`, or a tailored policy).
> - **Hosts with no MAC system (Arch, Gentoo, custom kernels):** the `lxc.apparmor.*` line is a no-op and can be omitted.
> - **cgroup v1 hosts:** if you later add cgroup-related directives, use `lxc.cgroup.*` (no `2`) instead of `lxc.cgroup2.*`. Most modern distros default to cgroup v2 — `stat -fc %T /sys/fs/cgroup/` returns `cgroup2fs` on v2.
>
> Use `docker info` and `docker run --rm hello-world` inside the container as the smoke test, and pick the smallest configuration that passes it on your distro.

Verify the final config looks correct:

```bash
cat /var/lib/lxc/claude-appliance/config
```

---

## Step 5 — Start and enter the container

```bash
sudo lxc-start -n claude-appliance
sudo lxc-attach -n claude-appliance
```

---

## Step 6 — Create the agent user

Ubuntu 24.04 creates a default `ubuntu` user at UID 1000 during container setup. Rename it to `claude-agent` and match your host UID so bind-mounted files have correct ownership.

First check your host UID:

```bash
# On the host
id <your-username>
# e.g. uid=1000(<your-username>) ...
```

Then inside the container:

```bash
# Rename ubuntu → claude-agent
usermod -l claude-agent ubuntu
usermod -d /home/claude-agent -m claude-agent
groupmod -n claude-agent ubuntu

# If your host UID is not 1000, adjust to match
# (skip these three lines if host UID is already 1000)
usermod -u <host-uid> claude-agent
groupmod -g <host-uid> claude-agent
chown -R claude-agent:claude-agent /home/claude-agent

# Always run this — grants passwordless sudo for apt-get and systemctl.
# Single write (not append) avoids duplicate lines on re-runs.
# 0440 is the only mode sudo will load files in /etc/sudoers.d/ with.
echo "claude-agent ALL=(ALL) NOPASSWD: /usr/bin/apt-get, /usr/bin/systemctl" \
  | tee /etc/sudoers.d/claude-agent > /dev/null
chmod 0440 /etc/sudoers.d/claude-agent

# Verify UID matches host
id claude-agent
```

> **Note on threat model.** This is "passwordless root inside the container" by design — see the threat model section at the top. The container, not the `claude-agent` user, is the security boundary; if you tighten this, you are hardening against an attack vector the design does not protect against in the first place.

---

## Step 7 — Install lean toolchain

> 🖥️ **Inside container** (as root)

```bash
apt update && apt install -y \
  git curl wget gnupg ca-certificates jq

# Set hostname
hostnamectl set-hostname claude-appliance

# GitHub CLI
curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg \
  | dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] \
  https://cli.github.com/packages stable main" \
  > /etc/apt/sources.list.d/github-cli.list
apt update && apt install -y gh

# Docker (full daemon — LXC handles the jail)
curl -fsSL https://get.docker.com | sh
systemctl enable docker
systemctl start docker

# kubectl
# Note: this fetches the current stable release. Pin to a specific version
# and verify the published .sha256 file if you want stricter supply-chain guarantees.
curl -fsSL https://dl.k8s.io/release/$(curl -fsSL https://dl.k8s.io/release/stable.txt)/bin/linux/amd64/kubectl \
  -o /usr/local/bin/kubectl
chmod +x /usr/local/bin/kubectl

# kind (Kubernetes IN Docker — for openemr-devops kubernetes work)
# Note: this fetches the latest release. Pin to a specific version and verify the
# SHA from https://github.com/kubernetes-sigs/kind/releases for stricter supply-chain guarantees.
curl -fsSL https://kind.sigs.k8s.io/dl/latest/kind-linux-amd64 \
  -o /usr/local/bin/kind
chmod +x /usr/local/bin/kind

# helm (used by kub-up to install cert-manager and NFS provisioner)
# Note: piping a remote install script to a shell is convenient but trusts the upstream;
# pin a specific Helm release if you want stricter supply-chain guarantees.
curl -fsSL https://raw.githubusercontent.com/helm/helm/main/scripts/get-helm-3 | bash

# openemr-cmd (clone openemr-devops into the bind-mounted git dir if not already present)
if [ ! -d "<git-dir>/openemr-devops" ]; then
  git clone https://github.com/openemr/openemr-devops.git <git-dir>/openemr-devops
fi
cp <git-dir>/openemr-devops/utilities/openemr-cmd/openemr-cmd /usr/local/bin/openemr-cmd
chmod +x /usr/local/bin/openemr-cmd

# Add claude-agent to docker group
usermod -aG docker claude-agent

# Node.js and Claude Code
curl -fsSL https://deb.nodesource.com/setup_lts.x | bash -
apt install -y nodejs
npm install -g @anthropic-ai/claude-code

# No PHP, Composer, or Avahi — not needed
```

---

## Step 8 — Set a static IP inside the container

> 🖥️ **Inside container** (as root)

Find the current DHCP-assigned IP and gateway:

```bash
ip addr show eth0     # note the current 10.0.3.x address
ip route show default # note the gateway e.g. 10.0.3.1
```

Pick a static address in the same range that won't conflict with other containers (e.g. `10.0.3.100`). Write directly to systemd-networkd — `netplan apply` doesn't work in LXC because it requires `udevadm`:

```bash
cat > /etc/systemd/network/10-static.network << 'EOF'
[Match]
Name=eth0

[Network]
Address=10.0.3.100/24
Gateway=10.0.3.1
DNS=10.0.3.1
FallbackDNS=8.8.8.8 1.1.1.1

[Route]
Gateway=10.0.3.1
EOF

systemctl restart systemd-networkd
sleep 5
```

Verify:

```bash
ip addr show eth0
# Should show 10.0.3.100 as a static address
ping -c 2 10.0.3.1
ping -c 2 8.8.8.8
ping -c 2 archive.ubuntu.com
```

---

## Step 9 — Add name resolution on the host

> 💻 **On the host machine**

```bash
sudo nano /etc/hosts
```

Add:

```
10.0.3.100   claude-appliance.local
```

Verify from the host:

```bash
ping claude-appliance.local
# Should get replies from 10.0.3.100
```

---

## Step 10 — Authenticate GitHub CLI

> 🖥️ **Inside container** (as claude-agent)

Use a **fine-grained personal access token** scoped to the minimum permissions needed. Create one at:

```
GitHub → Settings → Developer settings →
Personal access tokens → Fine-grained tokens → Generate new token
```

Select only the repos agents will work on (e.g. `openemr/openemr`, `openemr/openemr-devops`). Required permissions:

| Permission | Level |
|------------|-------|
| Metadata | Read (always required) |
| Pull requests | Read and write |
| Issues | Read and write |

> `Contents` is **not** needed — agents push branches via `git push` using the existing repo remote, not via the token. The token is only used by `gh` CLI for API operations (opening PRs, reading issues).

Set a 90-day expiration and rotate periodically. Then authenticate:

```bash
su - claude-agent
gh auth login
# Select: GitHub.com → HTTPS → Paste token
```

> The token alone does **not** grant `git push`. The fine-grained PAT has no `Contents` permission by design — it only authorizes API operations (PR creation, issue access). Pushes to your fork are handled separately by the deploy key set up in Step 10a.

---

## Step 10a — Deploy key for fork pushes

> 🖥️ **Inside container** (as claude-agent)

To let agents push branches to your fork from inside the container, use a **per-repo SSH deploy key** rather than a user-level SSH key. A user-level SSH key on GitHub inherits every permission your account has — including write access to upstream repositories you maintain. A deploy key is scoped to a single repository, so even if the in-container key is exposed the blast radius stops at that one repo.

For each fork the agent should push to (typically just `<your-username>/openemr`):

```bash
ssh-keygen -t ed25519 \
  -C "claude-appliance:<your-username>/openemr" \
  -f ~/.ssh/id_<your-username>_openemr -N ""

# Print the public half — paste this into GitHub
cat ~/.ssh/id_<your-username>_openemr.pub
```

On GitHub:

```
<your-username>/openemr → Settings → Deploy keys → Add deploy key
  Title: claude-appliance
  Key:   <paste public key>
  ☑ Allow write access
```

Tell SSH which key to use for that one repo. Append to `~/.ssh/config` (create if missing, `chmod 600`):

```
Host github-<your-username>-openemr
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_<your-username>_openemr
  IdentitiesOnly yes
```

Rewrite origin in the primary repo so worktrees inherit:

```bash
cd <git-dir>/openemr
git remote set-url origin git@github-<your-username>-openemr:<your-username>/openemr.git
```

Verify:

```bash
ssh -T git@github-<your-username>-openemr
# Expected: "Hi <your-username>/openemr! You've successfully authenticated,
#            but GitHub does not provide shell access."
```

The greeting naming the **repo** (not your username) confirms the key is repo-scoped — it cannot reach any other repository on GitHub. Push to upstream `openemr/openemr` or any other repo will be rejected at the protocol level.

> **Mirror the alias on the host.** Because `<git-dir>` is bind-mounted, the rewritten origin URL (`git@github-<your-username>-openemr:...`) lives in the shared `.git/config` and is visible from the host too. The host won't resolve the alias unless its own `~/.ssh/config` defines a matching `Host` stanza pointing at the host's regular SSH key:
>
> ```
> Host github-<your-username>-openemr
>   HostName github.com
>   User git
>   IdentityFile ~/.ssh/id_ed25519       # your normal host key
>   IdentitiesOnly yes
> ```
>
> Same alias name, different identity file. Container side uses the deploy key (repo-scoped); host side uses your full account key (unrestricted). Without this, host pushes/fetches will fail with "Could not resolve hostname".

> **Multiple forks?** Generate a separate deploy key per fork (`<your-username>/openemr-devops`, etc.). Each gets its own SSH config alias and origin URL inside the container, and a matching `Host` stanza on the host. Explicit per-repo authorization is the point.

---

## Step 10b — Optional: GPG signing inside the container

> 🖥️ **Inside container** (as claude-agent)

**This step is optional.** By default, agents commit unsigned and you re-sign on the host with `git commit --amend --no-edit -S` before pushing — every signed commit on your branch is then one a human has touched.

If host-side amending is more friction than you want, you can let the agent sign inside the container. Two trade-offs to weigh first:

- **Attestation blurs.** Agent commits will show as "verified" on GitHub under your name. The `Assisted-by: Claude Code` trailer still records AI involvement, but reviewers can no longer infer "human attested" from the signature alone.
- **Use a separate key.** Generate a fresh signing key for this purpose only — not your main identity's signing key. If the appliance is ever exposed, revocation is targeted to the container key and your main signing identity stays untouched.

If you accept those, generate the key in batch (loopback) mode:

```bash
gpg --batch --pinentry-mode loopback --passphrase "" \
  --quick-generate-key 'Your Name (claude-appliance) <your@email.com>' \
  default sign 1y
```

The email must match a verified email on your GitHub account. The empty passphrase is intentional — the LXC jail is the security boundary, and a passphrase the agent would have to type back doesn't add anything.

> **Why batch/loopback?** The interactive `gpg --full-generate-key` flow invokes pinentry, which fails inside LXC + `su -` sessions ("error calling pinentry: Permission denied") because pinentry can't reliably attach to the controlling terminal. Batch mode skips pinentry entirely.

Get the long key ID and full fingerprint:

```bash
gpg --list-secret-keys --keyid-format long
# Output:
#   sec   ed25519/ABCDEF1234567890 ...   <- 16-char long key ID
#         FULL40CHARFINGERPRINT...        <- full fingerprint (use this below)
```

Print the public half (this is safe — only the public key, the secret stays in the keyring):

```bash
gpg --armor --export ABCDEF1234567890
```

Copy the entire `-----BEGIN PGP PUBLIC KEY BLOCK-----` … `-----END PGP PUBLIC KEY BLOCK-----` block from your terminal, then paste it into GitHub under `Settings → SSH and GPG keys → New GPG key`.

Configure git to sign:

```bash
git config --global user.signingkey ABCDEF1234567890
git config --global commit.gpgsign true
git config --global tag.gpgsign true
```

Verify with a test commit:

```bash
cd <some-worktree>
git commit --allow-empty -m "test signing"
git log --show-signature -1
# Expected: "gpg: Good signature from ..."
```

> **To revoke later:** revoke the key on GitHub, then `gpg --delete-secret-keys ABCDEF1234567890` inside the container.

---

## Step 11 — Verify git path and worktree resolution

> 🖥️ **Inside container** (as claude-agent)

```bash
ls <git-dir>/
# Should see: openemr  plus zero or more openemr-wt-<branch-slug> directories
# (openemr-cmd derives the slug from the branch name — e.g. branch
# "feature/foo" becomes directory openemr-wt-feature-foo).

openemr-cmd worktree list
# Shows all worktrees, ports, and status. Pick one of the listed
# directories for the cd test below (skip if no worktrees yet).

# cd <git-dir>/openemr-wt-<branch-slug>
# git status
# Should work — .git pointer resolves to <git-dir>/openemr/.git
```

---

## Step 12 — Verify demo stack and port access from host

> 🖥️ **Inside container** (as claude-agent)

```bash
cd <git-dir>/openemr

# Create test worktree and start stack
openemr-cmd worktree add test-appliance -b --env easy --start

# Check assigned ports
openemr-cmd worktree list

# Verify PHP tooling runs inside the stack, not the appliance
openemr-cmd pp
openemr-cmd ut
```

> 💻 **On the host** — open browser and visit:

```
https://claude-appliance.local:9301
```

Click through the SSL warning (self-signed cert — connection is still encrypted). You should see the OpenEMR setup/login screen.

> 🖥️ **Inside container** — fully remove the test worktree (this is a one-time human-run setup verification, not an agent action):

```bash
echo "y" | openemr-cmd worktree remove test-appliance
```

> **Why `remove` here and not `down`?** This is a human-driven setup verification — the test worktree has no purpose afterwards. `worktree remove` (default, without `--keep-volumes`) runs `docker compose down --volumes`, which deletes the ~10 branch-scoped named volumes that openemr-cmd creates per worktree (`openemr-<slug>_db`, `_assets`, `_themes`, `_sites`, `_nodemodules`, `_vendor`, `_ccdanodemodules`, `_ccdanodemodules2`, `_logs`, `_couchdb`, plus `_mailpit` on non-light envs). Each worktree's volumes are fully namespaced — nothing is shared between worktrees — so removing `test-appliance` cleans up only its own volumes and leaves any other worktrees untouched. Using `worktree down --keep-volumes` here would orphan those volumes on disk indefinitely.
>
> Note: `CLAUDE.md` instructs *agents* never to run `openemr-cmd worktree remove`. This step is run by you, the human setting up the appliance, before any agent ever attaches.

---

## Step 13 — Snapshot the baseline

> 💻 **On the host**

```bash
# Stop container cleanly first
sudo lxc-stop -n claude-appliance

# Take snapshot
sudo lxc-snapshot -n claude-appliance \
  -c "baseline — lean toolchain, docker, gh, openemr-cmd, static IP, host /etc/hosts verified"

# Confirm snapshot saved
sudo lxc-snapshot -n claude-appliance -L

# Restart
sudo lxc-start -n claude-appliance
```

To restore if something goes wrong later:

```bash
sudo lxc-stop -n claude-appliance
sudo lxc-snapshot -n claude-appliance -r snap0
sudo lxc-start -n claude-appliance
```

> **Note:** The bind-mounted `<git-dir>` is not included in snapshots — it lives on the host. Snapshots protect the appliance configuration only.

---

## Step 14 — Agent launch script

Save as `<git-dir>/launch-agent.sh`:

```bash
#!/bin/bash
# Usage: ./launch-agent.sh <branch-name> [--env easy|easy-light|easy-redis]

set -euo pipefail

BRANCH="${1:-}"
ENV_NAME="easy"
OPENEMR_ROOT="<git-dir>/openemr"

if [[ -z "${BRANCH}" ]]; then
  echo "Usage: launch-agent.sh <branch-name> [--env easy|easy-light|easy-redis]" >&2
  exit 1
fi

# Parse optional "--env <value>" — passed as two separate args, validated
# against an allowlist so the value can never reach the shell unquoted.
if [[ $# -ge 3 && "$2" == "--env" ]]; then
  ENV_NAME="$3"
fi

case "${ENV_NAME}" in
  easy|easy-light|easy-redis) ;;
  *) echo "Invalid --env value: ${ENV_NAME}" >&2; exit 1 ;;
esac

export OPENEMR_ROOT

echo "==> Creating worktree and stack for: ${BRANCH} (env=${ENV_NAME})"
openemr-cmd worktree add "${BRANCH}" -b --env "${ENV_NAME}" --start

# Resolve the worktree path from openemr-cmd output rather than reconstructing
# the slug ourselves — keeps this script consistent with whatever naming
# scheme openemr-cmd actually produces.
WORKTREE_DIR="$(openemr-cmd worktree list \
  | awk -v branch="${BRANCH}" '$1 == branch { print $NF; exit }')"

if [[ -z "${WORKTREE_DIR}" ]]; then
  echo "Unable to determine worktree path for branch: ${BRANCH}" >&2
  exit 1
fi

echo "==> Stack ports:"
openemr-cmd worktree list | grep -F -- "${BRANCH}"

echo "==> Launching Claude Code agent in ${WORKTREE_DIR}"
cd "${WORKTREE_DIR}"

# --dangerously-skip-permissions disables Claude Code's per-action permission
# prompts. This is appropriate *inside this appliance* because the LXC + NAT
# boundary is the security model (see threat model section). Outside this
# context, leave the prompts on.
claude --dangerously-skip-permissions
```

---

## Step 15 — Multi-agent orchestrator (optional)

Save as `<git-dir>/orchestrate.sh`:

```bash
#!/bin/bash
# Spawn agents for GitHub issues labelled 'ai-agent'
# Usage: ./orchestrate.sh [--max-agents 3] [--env easy]

set -euo pipefail

MAX_AGENTS="${MAX_AGENTS:-3}"
ENV="easy"
REPO="openemr/openemr"

while [[ $# -gt 0 ]]; do
  case $1 in
    --max-agents) MAX_AGENTS=$2; shift 2 ;;
    --env)        ENV=$2; shift 2 ;;
    *) echo "Unknown arg: $1"; exit 1 ;;
  esac
done

echo "==> Fetching open issues labelled 'ai-agent' from ${REPO}"
ISSUES=$(gh issue list \
  --repo "${REPO}" \
  --state open \
  --label "ai-agent" \
  --json number,title \
  --limit "${MAX_AGENTS}")

echo "${ISSUES}" | jq -r '.[] | "\(.number) \(.title)"' | while read -r NUM TITLE; do
  BRANCH="agent/issue-${NUM}"

  if openemr-cmd worktree list | grep -q "${BRANCH}"; then
    echo "==> Skipping issue #${NUM} — worktree already exists"
    continue
  fi

  echo "==> Spawning agent for issue #${NUM}: ${TITLE}"
  bash <git-dir>/launch-agent.sh "${BRANCH}" --env "${ENV}" &

  # Stagger starts to avoid port collision during stack init
  sleep 5
done

wait
echo "==> All agents launched"
```

---

## Browser URL quick reference

| Service | Worktree 1 | Worktree 2 | Worktree 3 |
|---------|-----------|-----------|-----------|
| OpenEMR HTTPS | `https://claude-appliance.local:9301` | `:9302` | `:9303` |
| OpenEMR HTTP | `http://claude-appliance.local:8301` | `:8302` | `:8303` |
| phpMyAdmin | `http://claude-appliance.local:8311` | `:8312` | `:8313` |
| Mailpit | `http://claude-appliance.local:8026` | `:8027` | `:8028` |
| CouchDB | `http://claude-appliance.local:5985` | `:5986` | `:5987` |

> SSL warning on HTTPS is expected — the dev stack uses a self-signed cert. The connection is still encrypted. Click through once; Firefox lets you add a permanent exception.

---

## Notes

**Static IP collision avoidance:** `lxcbr0` typically uses `10.0.3.x`. If you run multiple LXC containers, assign each a distinct static IP (e.g. `10.0.3.100`, `10.0.3.101`). With a single `claude-appliance` container there is no conflict risk.

**Internet access:** The container reaches the internet via the host's NAT — works on any connection including mobile hotspot. No special networking on the host is required.

**If you ever want bridge + mDNS instead:** The switch is non-destructive. Configure `br0` on the host, update the LXC config to use `lxc.net.0.link = br0`, remove the static IP netplan config inside the container, and delete the `/etc/hosts` line. The NAT approach has no downsides for a single-developer setup.
