# MedEx Operations Guide

Last updated: March 25, 2026

## Table of Contents

1. [What MedEx Handles](#what-medex-handles)
2. [Start Here (Connection)](#start-here-connection)
3. [Daily Workflow](#daily-workflow)
4. [A La Carte Credits: What They Are](#a-la-carte-credits-what-they-are)
5. [How Credits Are Used](#how-credits-are-used)
6. [Why Auto-Renew Should Be Enabled](#why-auto-renew-should-be-enabled)
7. [How To Set Auto-Renew](#how-to-set-auto-renew)
8. [Low-Balance and Failed-Send Recovery](#low-balance-and-failed-send-recovery)
9. [When To Contact Support](#when-to-contact-support)
10. [Onboarding URL and Callback Checks](#onboarding-url-and-callback-checks)

---

## What MedEx Handles

MedEx runs patient communication workflows inside OpenEMR:

- appointment reminders
- recall outreach
- SMS reply handling
- campaign/announcement sends
- secure communication workflows

## Start Here (Connection)

Before sending anything, confirm MedEx status is **Online**.

1. Open Module Manager.
2. Click the MedEx gear icon.
3. Verify status:
   - **Online**: ready
   - **Offline**: reconnect in Settings
   - **Disabled**: enable module first

If not Online, fix that first. Sending while Offline causes failed workflows and retry noise.

## Daily Workflow

Recommended operating sequence:

1. Check connection status.
2. Check A La Carte balance.
3. Launch reminders/recalls/campaigns.
4. Review send status and responses.
5. Resolve failures same day.

## A La Carte Credits: What They Are

A La Carte Credits are your messaging fuel.

Think of them as a single shared balance used by outbound communication activity.

If credits are unavailable, message workflows do not complete reliably.

## How Credits Are Used

Credits are consumed by outbound messaging workflows, including reminder and campaign sends.

Operationally, the same pool is used across communication activity. That means one busy campaign window can drain balance and affect other sends unless monitored.

In practice:

- reminder traffic uses credits
- recall/campaign traffic uses credits
- announcement-style sends use credits

If balance drops too low, sends can fail, queue, or be blocked depending on workflow state.

## Why Auto-Renew Should Be Enabled

Auto-renew is recommended for reliability, not convenience.

Without auto-renew:

- a balance drop can stop reminders mid-cycle
- recalls can stall during high-volume periods
- staff must manually intervene during active operations
- patient communication continuity is at risk

With auto-renew:

- sends continue through normal volume spikes
- reminder/recall continuity is preserved
- staff workload and emergency rework are reduced
- fewer same-day failures hit front desk workflows

## How To Set Auto-Renew

Use the A La Carte Credits controls in MedEx admin.

1. Open MedEx dashboard/settings area.
2. Open **A La Carte Credits**.
3. Enable **Auto-Renew**.
4. Set:
   - renew trigger threshold (when refill should occur)
   - refill amount (how much to add each cycle)
5. Save settings.
6. Re-open the card and confirm values persisted.

Recommended setup pattern:

- trigger threshold high enough to cover one business day of peak sends
- refill amount sized for your typical weekly communication volume

## Low-Balance and Failed-Send Recovery

If balance-related failures are already happening:

1. Recharge credits first.
2. Confirm updated balance is visible.
3. Re-run failed batches in small groups.
4. Verify successful sends before relaunching full volume.
5. Enable/adjust auto-renew so this does not recur.

## When To Contact Support

Contact support if:

- credits look sufficient but sends still fail
- auto-renew is enabled but not executing
- balance values are inconsistent across pages
- status remains degraded after recharge and retry

Include in your support email:

- exact URL
- timestamp
- action attempted
- screenshot of balance/status

Support: `support@medexbank.com`

## Onboarding URL and Callback Checks

Production onboarding requires a public HTTPS OpenEMR URL.

- Use an FQDN with valid TLS.
- DNS must resolve publicly.
- Port 443 must be reachable from the internet.

Developer-only local testing (no public FQDN):

1. Enable developer mode in OpenEMR globals:

```sql
REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_onboarding_dev_mode', 0, '1');
```

2. Use a tunnel URL as the OpenEMR URL in onboarding:

```bash
cloudflared tunnel --url http://localhost:8300
```

or

```bash
ngrok http 8300
```

3. Disable developer mode before production go-live:

```sql
REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_onboarding_dev_mode', 0, '0');
```
