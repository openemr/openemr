#!/usr/bin/env bash

# Pinned upstream SHA of openemr/oe-smart-questionnaire that run.sh fetches.
# A drift-check workflow compares this against the upstream master HEAD and
# files an issue on drift. Bump explicitly when a maintainer confirms the
# new revision still passes the smoke; never auto-advance.
#
# Current pin: 2026-10-11 — the initial commit of the repo (upstream has a
# single commit so far).
#
# Consumers source this file and read SMART_QUESTIONNAIRE_PIN; shellcheck
# can't see that from here.
# shellcheck disable=SC2034
SMART_QUESTIONNAIRE_PIN=cb838d03087134aff47d5c2485bef1536b094287
