# Docker Sandboxes Setup for AI Coding Agents

One reference configuration for running AI coding agents against OpenEMR in an
isolated environment. Each agent runs in a microVM with its own kernel, its own
Docker daemon, and its own network stack, so it can bring up a full OpenEMR demo
stack without touching your host Docker or your host filesystem outside the
directory you share with it.

This is an alternative to the
[LXC appliance configuration](claude-appliance-setup.md), not a replacement for
it. Both satisfy the universal rules in the repository's `CLAUDE.md`. Pick
whichever fits your machine and your comfort with the tradeoffs below.

## Why this approach

- **Hypervisor boundary.** Each sandbox is a microVM with its own kernel, not a
  container sharing yours. Nested Docker needs no privileged container and no
  relaxed AppArmor or SELinux policy.
- **Short setup.** Install one package, sign in, run two commands.
- **Outbound network policy** is built in (`Open`, `Balanced`, `Locked Down`,
  plus per-host allow and deny rules).
- **Credentials stay on the host.** GitHub tokens are attached to outbound
  requests by a host-side proxy and never enter the sandbox filesystem.

## Tradeoffs to know before you start

- **Requires Ubuntu 24.04 or later** (or another platform Docker supports for
  `sbx`) with KVM available. It will not install on Ubuntu 22.04.
- **Requires a Docker account.** Sign-in is mandatory. See the note on separate
  accounts below if you are a member of the OpenEMR Docker organization.
- **The `sbx` CLI collects basic usage telemetry** (command name, success or
  failure, duration, and your Docker username). Set `SBX_NO_TELEMETRY=1` to opt
  out. Docker states it does not read prompts or code.
- **The agent can push to your fork but cannot open the pull request** — a
  fine-grained token covers one resource owner. See
  [Opening pull requests](#opening-pull-requests).
- **SSH deploy keys do not work** from inside a sandbox — see
  [Push access](#push-access).
- **Ports are not published to the host automatically** — see
  [Step 8](#step-8--publish-ports-to-your-host).

## Requirements

- Ubuntu 24.04 or later, x86-64 or arm64
- KVM available and your user in the `kvm` group
- A Docker account (free)
- A fork of `openemr/openemr` on GitHub

Check KVM:

```bash
ls -l /dev/kvm
groups | grep -q kvm && echo "in kvm group" || sudo usermod -aG kvm "$USER"
```

Log out and back in, or run `newgrp kvm`, if you had to add yourself.

## Step 1 — Install sbx

If you already run Docker Engine on this host, add Docker's apt repository
without reinstalling the engine:

```bash
curl -fsSL https://get.docker.com | sudo REPO_ONLY=1 sh
sudo apt install docker-sbx
```

To install Docker Engine and `sbx` together:

```bash
curl -fsSL https://get.docker.com | sudo SBX=1 sh
```

> If a previous release upgrade left Docker's apt repository pinned to an older
> Ubuntu codename, `apt` will not find `docker-sbx`. Re-run the `REPO_ONLY=1`
> line above to refresh it, and confirm `/etc/apt/sources.list.d/docker.list`
> names your current codename.

## Step 2 — Sign in

```bash
sbx login
```

This prints a device code and a URL. Open the URL in a browser and enter the
code — no browser is needed on the machine running `sbx`, which matters if you
run it as a dedicated user (below).

> **If you are a member of the OpenEMR organization on Docker Hub, use a
> separate Docker account for sandboxes.** On hosts without a desktop keyring
> the credential is stored in a file. An account that can push images to the
> OpenEMR organization should not have its token sitting in a file used by an
> autonomous agent. A free second account with no organization membership is
> enough.

On first `sbx run` you are asked to pick a global network policy. `Balanced`
(default deny with common developer sites allowed) is sufficient for OpenEMR
work — it did not block image pulls, Composer, or npm during testing. Change it
later with `sbx policy`.

## Step 3 — Recommended: a dedicated user account

The microVM is what isolates the agent's processes, so this step is not what
makes the setup safe. What it does is bound what the agent can reach *through*
credentials — and that matters more the more access you have.

If you hold commit rights beyond your own fork, your account can reach every
repository you maintain, your servers, and whatever else your keys and tokens
open. Running agents under a separate account means their reach is defined by
one narrowly scoped token instead: a distinct GitHub token limited to your fork,
a distinct Docker account, no access to your SSH keys, and nothing of yours in
the environment.

It also keeps you out of the shared git directory by accident — see
[The shared git directory is not protected](#the-shared-git-directory-is-not-protected).

Four commands:

```bash
sudo useradd -m -s /bin/bash openemr-agent
sudo usermod -aG kvm openemr-agent
sudo chmod 750 /home/openemr-agent
sudo chmod 750 "$HOME"          # if your home directory is world-readable
```

Enter it with `sudo -u openemr-agent -i`. Confirm the separation:

```bash
sudo -u openemr-agent ls "$HOME"   # expect: Permission denied
```

Do **not** add this user to the `docker` group. Docker group membership is
root-equivalent on the host, and the sandbox provides its own daemon.

Everything below is then done as that user. `sbx login`, the network policy, and
stored secrets are all per-user.

If you skip this step, the rest still works — run everything as yourself, and
drop the `sudo` from the file handoff in
[Opening pull requests](#opening-pull-requests).

## Step 4 — Lay out the git directory

`openemr-cmd` creates worktrees as *siblings* of the primary repository:

```
~/git/
├── openemr/                  # primary repo
├── openemr-wt-<slug>/        # worktrees created by openemr-cmd
└── sbx-bootstrap.sh          # see Step 6
```

```bash
mkdir -p ~/git && cd ~/git
git clone https://github.com/<your-username>/openemr.git
```

## Step 5 — Start a sandbox from the git parent directory

**Run `sbx` from `~/git`, not from `~/git/openemr`.**

`sbx` mounts the directory you invoke it in. If you start it inside the
repository, the sandbox cannot write to the parent, and
`openemr-cmd worktree add` fails with:

```
fatal: could not create leading directories of
'.../openemr-wt-<slug>/.git': Permission denied
```

Starting from the parent mounts the whole git directory, so sibling worktrees
can be created normally.

```bash
cd ~/git
sbx run claude
```

Substitute another agent name if you use a different tool — `sbx run shell`
gives a plain shell, which is useful for verifying the stack without an agent.
Run `sbx run --help` for the current list.

## Step 6 — Bootstrap the sandbox

Only the git directory is shared with the sandbox. Everything else in a
sandbox's filesystem is discarded when it is removed, so `openemr-cmd` — which
lives in `openemr-devops`, not in this repository — must be installed in each
new sandbox.

Save the following as `~/git/sbx-bootstrap.sh` and `chmod +x` it. Because it
lives in the git directory, it is visible from inside every sandbox.

````bash
#!/bin/bash
# sbx-bootstrap.sh — run INSIDE a Docker Sandbox, once per sandbox.
#
#   cd ~/git && sbx run claude       # or: sbx run shell
#   ./sbx-bootstrap.sh               # inside the sandbox
#
# Installs openemr-cmd and creates an agent rules file in the git parent
# directory if one is not already there. Safe to re-run.

set -euo pipefail

GIT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEVOPS_DIR="${HOME}/openemr-devops"
RULES_FILE="${GIT_DIR}/CLAUDE.md"

echo "==> git directory: ${GIT_DIR}"

if [[ ! -d "${GIT_DIR}/openemr" ]]; then
  echo "No openemr checkout found at ${GIT_DIR}/openemr." >&2
  echo "Run this from the git parent directory, not from inside the repo." >&2
  exit 1
fi

if command -v openemr-cmd >/dev/null 2>&1; then
  echo "==> openemr-cmd already installed: $(openemr-cmd --version)"
else
  echo "==> installing openemr-cmd"
  if [[ -d "${DEVOPS_DIR}/.git" ]]; then
    git -C "${DEVOPS_DIR}" pull --ff-only
  else
    git clone --depth 1 https://github.com/openemr/openemr-devops.git "${DEVOPS_DIR}"
  fi
  sudo install -m 0755 \
    "${DEVOPS_DIR}/utilities/openemr-cmd/openemr-cmd" \
    /usr/local/bin/openemr-cmd
  echo "==> installed: $(openemr-cmd --version)"
fi

if [[ -f "${RULES_FILE}" ]]; then
  echo "==> rules file already present: ${RULES_FILE}"
else
  echo "==> writing ${RULES_FILE}"
  cat > "${RULES_FILE}" <<'RULES'
# Agent rules — OpenEMR in a Docker Sandbox

## Read first

The authoritative rules for this codebase are in `openemr/CLAUDE.md`. Read that
file before making any change. It covers the worktree lifecycle, the
`openemr-cmd` devtools, port offsets, and the golden rules. Everything below is
specific to running inside a Docker Sandbox and does not replace it.

## Orientation

```bash
git rev-parse --show-toplevel   # confirm which repo you are in
openemr-cmd worktree list       # worktrees, status, assigned ports
```

## Sandbox specifics

- The git parent directory is mounted from the host. Worktrees created by
  `openemr-cmd` are siblings of `openemr/` and live on the host filesystem.
- `openemr-cmd` is installed per sandbox by `sbx-bootstrap.sh`. If it is
  missing, run that script rather than installing it by hand.
- Ports are not published to the host automatically. After starting a stack,
  tell the human the assigned ports and that they need to run
  `sbx ports <sandbox-name> --publish <port>:<port>` on the host. You cannot do
  this from inside the sandbox.
- Git pushes go over HTTPS; the token is attached by a host-side proxy. Do not
  try to configure SSH keys, and do not tell the human to push from their host.
- You cannot open a pull request against `openemr/openemr`. Push the branch to
  the fork, then write the PR body to `<git-dir>/pr-<branch-slug>.md` as plain
  markdown — no title line, no code fences, no script — and tell the human the
  file name and a suggested title. Use one file per branch.

## Rules

- Use `openemr-cmd` for all worktree and stack operations. Do not call
  `git worktree add/remove` or `docker compose` directly — it will corrupt the
  state `openemr-cmd` manages.
- Never push to `master` or `main`, or to any branch you did not create.
- Open pull requests as drafts unless explicitly asked otherwise.
- Never run `openemr-cmd worktree remove` on your own initiative. It deletes the
  worktree directory and its Docker volumes, including the database. Only run it
  when the human names a specific worktree, and only after confirming
  `git status --porcelain` and `git log @{upstream}..` are both empty there.
- Do not edit `.worktrees.json` by hand.
RULES
fi

echo
echo "==> ready"
echo "    openemr-cmd : $(command -v openemr-cmd)"
echo "    rules file  : ${RULES_FILE}"
````

Run it inside the sandbox:

```bash
./sbx-bootstrap.sh
```

The rules file it writes lives in the git directory, so it persists across
sandboxes and is written only once. The `openemr-cmd` install is per sandbox.

### Why the rules file sits in the git parent directory

Agents are launched from the git parent so that sibling worktrees can be created
(Step 5), which is one level above the repository's own `CLAUDE.md`. A rules
file at the launch directory is picked up at startup and points the agent at
`openemr/CLAUDE.md`, which remains the authoritative source. Edit the file
freely — the script will not overwrite an existing one.

## Step 7 — Create a worktree and start a stack

From the repository directory inside the sandbox:

```bash
cd <git-dir>/openemr
openemr-cmd worktree add <branch-name> -b --env easy-light --start
openemr-cmd worktree list
```

`worktree list` reports the assigned ports. The first boot of the OpenEMR flex
image runs Composer and npm and takes several minutes; watch it with
`openemr-cmd dl`.

Confirm it is serving from inside the sandbox before testing from your browser:

```bash
curl -k -I https://localhost:9301
```

A `302` to `interface/login/login.php` means the stack is up. A broken pipe or
connection reset means it is still building.

## Step 8 — Publish ports to your host

Sandbox ports are **not** published to the host automatically, and this cannot
be done from inside the sandbox. From the host:

```bash
sbx ls                                              # find the sandbox name
sbx ports <sandbox-name> --publish 9301:9301 --publish 8311:8311
sbx ports <sandbox-name>                            # confirm
```

Ports can be published on a running sandbox — no restart needed. Publishing
without a protocol binds `127.0.0.1` only; if your browser resolves `localhost`
to `::1` and refuses to connect, use `https://127.0.0.1:9301`, or publish with
an explicit `/tcp` to bind both address families.

Open `https://localhost:9301` and click through the self-signed certificate
warning. You should see the OpenEMR login screen.

Publish the ports for each worktree offset you use. At offset 1 that is 9301
(HTTPS), 8301 (HTTP), 8311 (phpMyAdmin), and 8321 (MySQL); see the port table in
`CLAUDE.md` for the offset formulas and the remaining services.

## Push access

SSH deploy keys do **not** work from inside a sandbox. The sandbox mounts only
your git directory, so it cannot read `~/.ssh/config` and cannot resolve a host
alias defined there.

Use an HTTPS remote and let `sbx` broker the token instead. The token is
attached by a host-side proxy and never enters the sandbox filesystem.

1. Create a fine-grained personal access token on GitHub with the resource owner
   set to **your own account**, scoped to **only your fork**, with:

   | Permission | Level |
   |------------|-------|
   | Metadata | Read |
   | Contents | Read and write |
   | Pull requests | Read and write |
   | Issues | Read and write |

2. Authenticate `gh` on the host with that token:

   ```bash
   gh auth login        # GitHub.com → HTTPS → paste an authentication token
   ```

3. Store it as a sandbox secret. Storing the *command* rather than the value
   means a rotated token is picked up automatically:

   ```bash
   sbx secret set github --command 'gh auth token'
   sbx secret ls
   ```

4. Use an HTTPS origin in your clone:

   ```bash
   cd ~/git/openemr
   git remote set-url origin https://github.com/<your-username>/openemr.git
   ```

Verify from inside a sandbox before relying on it:

```bash
git push origin HEAD:refs/heads/sbx-auth-test --dry-run
```

## The shared git directory is not protected

The microVM isolates the agent's processes, filesystem, and network. The
directory you mount is shared by design, and the agent can write anywhere in it
— the whole git directory, not just the repository: every worktree, the
bootstrap script, and anything else you keep there.

That includes git's own configuration. `.git/config` values such as
`core.sshCommand`, `core.hooksPath`, and `credential.helper`, scripts under
`.git/hooks/`, and filter drivers declared in `.gitattributes` all cause git to
execute commands, and they are ordinary files in the shared directory. If you
run a git command against that clone from your own account — push, fetch,
checkout, or commit — git acts on whatever those files say, with your
credentials.

No sandbox boundary prevents this, because nothing escapes: the agent writes a
file in a directory you chose to share, and your own git command reads it.

**Keep a separate clone outside the shared directory** for git work you do under
your own identity — signing, pushing to remotes you care about, or anything
using your personal SSH key. Fetch the agent's branch there and work from there.
Treat the shared directory as the agent's.

The dedicated user account in [Step 3](#step-3--recommended-a-dedicated-user-account)
mitigates the accidental version of this: with the agent's home directory at
mode 750, your own account cannot read into the shared directory at all, so you
cannot wander into it and run git by habit. Reaching it deliberately, with
`sudo`, still works — which is what the pull request handoff below relies on.

## Opening pull requests

An agent inside a sandbox can push to your fork but cannot open a pull request
against `openemr/openemr`, because of how the credential is scoped rather than
any sandbox limitation.

Creating a cross-repo pull request needs access to both repositories: the head
repository to read the branch, and the base repository to open the request. A
fine-grained token belongs to a single resource owner, so one token cannot cover
both your fork and the organization's repository. `sbx` stores one `github`
secret per sandbox, so the agent has one identity to work with.

Keeping the sandbox token scoped to your fork alone is the right trade. The
practical workflow:

1. The agent pushes the branch to your fork.
2. The agent writes the PR body to `<git-dir>/pr-<branch-slug>.md` as plain
   markdown, and suggests a title.
3. You read that file and pass it to `gh pr create --body-file` on the host,
   from a clone outside the shared directory, authenticated as yourself.

For example:

```bash
sudo -u <agent-user> cat /home/<agent-user>/git/pr-<branch-slug>.md > /tmp/body.md
cd ~/src/openemr                       # your own clone, not the shared one
git fetch origin <branch-name>
gh pr create --repo openemr/openemr --draft \
  --head <your-username>:<branch-name> \
  --title "<title>" \
  --body-file /tmp/body.md
```

Have the agent write a body file. The body is read as data. Supplying the title
yourself is also worth it — it is the first thing a reviewer sees.

A per-branch filename matters if you run several agents at once. The rules file
written by `sbx-bootstrap.sh` already instructs agents to follow this pattern.

Reading works normally, so the review cycle is not affected: an agent can run
`gh pr view <n> --repo openemr/openemr --comments` and `gh pr checks`, act on
the feedback, and push fixes to the fork — which updates the pull request.

## Notes and limitations

- Sandboxes do not inherit your user-level agent configuration (for example
  files under `~/.claude`). Configuration in the workspace is available inside
  the sandbox, which is what the rules file in Step 6 relies on.
- Agents run without approval prompts by default, because the sandbox is the
  boundary. To re-enable prompts, change the permission mode inside the session,
  or define a sandbox kit that drops the permission-skipping flag from the
  agent's entrypoint.
- Docker's sandbox kit and skill features are Early Access and Experimental
  respectively. A kit could package `openemr-cmd` and replace the bootstrap
  script, but that is not documented here because those interfaces may change.
- Running several agents in parallel is possible but has not been verified in
  this configuration. Note that Claude Code and the Claude apps draw from one
  subscription allowance, so parallel agents consume it proportionally faster.

## Verified configuration

Tested on Ubuntu 24.04.5 with the `easy-light` environment: sandbox creation,
bootstrap, worktree creation, stack start, browser access from the host over a
published port, a branch push to a fork, and the pull request workflow above.
The `easy` and `easy-redis` environments and multi-worktree parallel operation
have not been tested under `sbx`.
