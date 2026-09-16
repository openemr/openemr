#!/usr/bin/env python3
"""Render a ScanCode JSON report as a GitHub Actions step summary.

Reads the ScanCode JSON report passed as the first argument and appends a
markdown summary to ``$GITHUB_STEP_SUMMARY`` (or stdout if the variable is
unset — useful for local testing).

Policy enforcement is driven by a checked-in JSON policy file (see
``tools/ci/license-policy.json``). Files whose detected licenses violate the
policy are annotated with ``::error file=...,line=...::`` so the finding lands
on the offending lines in the PR "Files changed" view, and the job exits
non-zero.

Environment variables:
    SCAN_MODE   Label shown in the summary heading ("diff" or "full").
    PER_FILE_LIMIT
                Maximum number of per-file rows to include in the collapsible
                details block. Defaults to 500.
    SCANCODE_POLICY_FILE
                Path to the license policy JSON. Unset / empty disables
                enforcement (informational mode).
"""
from __future__ import annotations

import json
import os
import re
import sys
from collections import Counter

# ScanCode license expressions are SPDX-like: keys joined by boolean operators
# and grouped with parentheses. Split on those so policy is evaluated against
# individual license keys rather than the composed expression string — a file
# whose detection composes three keys must trip a policy that denies any one
# of them, which a set-membership test on the whole expression would miss.
EXPRESSION_SPLIT_RE = re.compile(r'[\s()]+')
EXPRESSION_OPERATORS = frozenset({'AND', 'OR', 'WITH'})

SCAN_ROOT_PREFIXES = ('scancode-input/', 'scancode-shard-input/')

# Cap the per-annotation detail so a file matching a dozen rules does not
# produce an unreadable wall of text in the PR diff view.
MAX_RULES_PER_ANNOTATION = 3


def parse_license_keys(expression: str | None) -> list[str]:
    """Return the individual license keys in a ScanCode license expression."""
    if not expression:
        return []
    keys = []
    for token in EXPRESSION_SPLIT_RE.split(expression):
        if not token or token.upper() in EXPRESSION_OPERATORS:
            continue
        if token not in keys:
            keys.append(token)
    return keys


def strip_scan_root(path: str) -> str:
    """Normalize the scan-root prefix so paths match the repo layout."""
    for prefix in SCAN_ROOT_PREFIXES:
        if path.startswith(prefix):
            return path[len(prefix):]
    return path


def escape_data(value: str) -> str:
    """Escape a GitHub Actions workflow-command message body."""
    return value.replace('%', '%25').replace('\r', '%0D').replace('\n', '%0A')


def escape_property(value: str) -> str:
    """Escape a GitHub Actions workflow-command property value.

    Properties are comma-separated ``key=value`` pairs terminated by ``::``,
    so commas and colons inside a value must be encoded or they truncate the
    annotation.
    """
    return escape_data(value).replace(':', '%3A').replace(',', '%2C')


class Policy:
    """License policy loaded from JSON. See tools/ci/license-policy.json."""

    def __init__(self, path: str, data: dict) -> None:
        self.path = path
        self.url = data.get('policy_url', '')
        self.deny: dict[str, str] = data.get('deny', {}) or {}
        self.deny_categories: dict[str, None] = {
            c: None for c in data.get('deny_categories', []) or []
        }
        self.category_reasons: dict[str, str] = data.get('category_reasons', {}) or {}

    @classmethod
    def load(cls, path: str) -> Policy:
        with open(path) as fh:
            return cls(path, json.load(fh))

    @property
    def uses_categories(self) -> bool:
        return bool(self.deny_categories)

    def violation_reason(self, key: str, category: str | None) -> str | None:
        """Return why ``key`` is denied, or None if the policy permits it."""
        if key in self.deny:
            return self.deny[key] or 'Denied by license policy.'
        if category is not None and category in self.deny_categories:
            return self.category_reasons.get(
                category,
                f'Licenses in the "{category}" category are not permitted.',
            )
        return None

    def describe(self) -> list[str]:
        lines = [f'Policy: [`{self.path}`]({self.url})' if self.url
                 else f'Policy: `{self.path}`', '']
        if self.deny_categories:
            cats = ', '.join(f'`{c}`' for c in self.deny_categories)
            lines.append(f'- Denied categories: {cats}')
        if self.deny:
            keys = ', '.join(f'`{k}`' for k in sorted(self.deny))
            lines.append(f'- Denied license keys: {keys}')
        lines.append('- Everything else is allowed.')
        lines.append('')
        return lines


class Violation:
    def __init__(
        self,
        path: str,
        key: str,
        name: str,
        category: str | None,
        reason: str,
        reference_url: str,
        matches: list[dict],
    ) -> None:
        self.path = path
        self.key = key
        self.name = name
        self.category = category
        self.reason = reason
        self.reference_url = reference_url
        # Strongest evidence first — that is what the annotation anchors to.
        self.matches = sorted(
            matches, key=lambda m: m.get('score') or 0, reverse=True
        )

    @property
    def best(self) -> dict:
        return self.matches[0]

    def title(self) -> str:
        label = self.name or self.key
        return f'Disallowed license: {label} ({self.key})'

    def message(self) -> str:
        best = self.best
        category = f' [{self.category}]' if self.category else ''
        lines = [
            f'Detected license `{self.key}`{category}'
            + (f' — {self.name}' if self.name else '')
            + '.',
            f'{self.reason}',
            '',
            'Evidence:',
        ]
        for match in self.matches[:MAX_RULES_PER_ANNOTATION]:
            start = match.get('start_line')
            end = match.get('end_line')
            span = f'line {start}' if start == end else f'lines {start}-{end}'
            lines.append(
                f'  - {span}: {match.get("score")}% score, '
                f'{match.get("match_coverage")}% coverage, '
                f'rule {match.get("rule_identifier")}'
            )
        extra = len(self.matches) - MAX_RULES_PER_ANNOTATION
        if extra > 0:
            lines.append(f'  - ...and {extra} further match(es).')
        lines.append('')
        if self.reference_url:
            lines.append(f'About this license: {self.reference_url}')
        rule_url = best.get('rule_url')
        if rule_url:
            lines.append(f'Matching rule: {rule_url}')
        return '\n'.join(lines)

    def annotation(self, policy: Policy) -> str:
        best = self.best
        body = self.message()
        location = f'See {policy.url}' if policy.url else ''
        body += f'\nAllowed vs. denied licenses are defined in {policy.path}.'
        if location:
            body += f' {location}'
        props = [
            f'file={escape_property(self.path)}',
            f'title={escape_property(self.title())}',
        ]
        start = best.get('start_line')
        end = best.get('end_line')
        if isinstance(start, int):
            props.insert(1, f'line={start}')
            if isinstance(end, int) and end >= start:
                props.insert(2, f'endLine={end}')
        return f'::error {",".join(props)}::{escape_data(body)}'


def build_reference_index(data: dict) -> dict[str, dict]:
    """Index ``license_references`` by license key.

    Populated by ScanCode's ``--license-references`` option; supplies the
    human-readable name, category, and LicenseDB URL used in annotations.
    """
    index = {}
    for ref in data.get('license_references', []) or []:
        key = ref.get('key')
        if key:
            index[key] = ref
    return index


def collect_violations(
    data: dict, policy: Policy, refs: dict[str, dict]
) -> tuple[list[Violation], Counter[str], list[tuple[str, list[str]]]]:
    licenses: Counter[str] = Counter()
    per_file: list[tuple[str, list[str]]] = []
    violations: list[Violation] = []

    for entry in data.get('files', []):
        if entry.get('type') != 'file':
            continue
        detections = entry.get('license_detections', []) or []
        if not detections:
            continue

        path = strip_scan_root(entry['path'])
        expressions = sorted({
            d.get('license_expression', 'unknown') for d in detections
        })
        per_file.append((path, expressions))
        for expr in expressions:
            licenses[expr] += 1

        # Group every match by the individual license key it attributes, so a
        # compound detection is judged key by key.
        by_key: dict[str, list[dict]] = {}
        for detection in detections:
            for match in detection.get('matches', []) or []:
                for key in parse_license_keys(match.get('license_expression')):
                    by_key.setdefault(key, []).append(match)

        for key, matches in sorted(by_key.items()):
            ref = refs.get(key, {})
            category = ref.get('category')
            reason = policy.violation_reason(key, category)
            if reason is None:
                continue
            violations.append(Violation(
                path=path,
                key=key,
                name=ref.get('short_name') or ref.get('name') or '',
                category=category,
                reason=reason,
                reference_url=ref.get('licensedb_url')
                or f'https://scancode-licensedb.aboutcode.org/{key}',
                matches=matches,
            ))

    return violations, licenses, per_file


def render_summary(
    mode: str,
    licenses: Counter[str],
    per_file: list[tuple[str, list[str]]],
    violations: list[Violation],
    policy: Policy | None,
    limit: int,
) -> str:
    lines: list[str] = [f'## ScanCode License Findings ({mode} scan)', '']

    if violations:
        lines.append(f'### ❌ {len(violations)} policy violation(s)')
        lines.append('')
        lines.append('| File | License | Category | Lines | Why |')
        lines.append('|---|---|---|---|---|')
        for v in violations:
            best = v.best
            start, end = best.get('start_line'), best.get('end_line')
            span = f'{start}' if start == end else f'{start}-{end}'
            label = f'`{v.key}`' + (f' ({v.name})' if v.name else '')
            lines.append(
                f'| `{v.path}` | [{label}]({v.reference_url}) '
                f'| {v.category or "unknown"} | {span} | {v.reason} |'
            )
        lines.append('')

    if policy is not None:
        lines.extend(policy.describe())

    if not licenses:
        lines.append('No license text detected.')
    else:
        lines.append('| License Expression | File Count |')
        lines.append('|---|---|')
        lines.extend(f'| `{expr}` | {count} |' for expr, count in licenses.most_common())
        lines.append('')
        shown = per_file[:limit]
        heading = 'Per-file findings'
        if len(per_file) > limit:
            heading = f'Per-file findings (first {limit} of {len(per_file)})'
        lines.append(f'<details><summary>{heading}</summary>')
        lines.append('')
        lines.append('| File | License(s) |')
        lines.append('|---|---|')
        lines.extend(
            f'| `{path}` | {", ".join(f"`{e}`" for e in exprs)} |'
            for path, exprs in shown
        )
        lines.append('')
        lines.append('</details>')

    return '\n'.join(lines) + '\n'


def main(argv: list[str]) -> int:
    if len(argv) != 2:
        print(f'usage: {argv[0]} <scancode-results.json>', file=sys.stderr)
        return 2

    results_path = argv[1]
    mode = os.environ.get('SCAN_MODE', 'unknown')
    limit = int(os.environ.get('PER_FILE_LIMIT', '500'))
    policy_file = os.environ.get('SCANCODE_POLICY_FILE', '').strip()

    with open(results_path) as fh:
        data = json.load(fh)

    policy = Policy.load(policy_file) if policy_file else None
    refs = build_reference_index(data)

    if policy is not None and policy.uses_categories and not refs:
        # Without license_references there are no categories, so a
        # category-based policy would silently pass everything.
        print(
            f'::error::{policy.path} denies license categories but the '
            'ScanCode report contains no license_references — rerun the scan '
            'with --license-references.',
            file=sys.stderr,
        )
        return 2

    if policy is None:
        violations, licenses, per_file = [], Counter(), []
        for entry in data.get('files', []):
            if entry.get('type') != 'file':
                continue
            detections = entry.get('license_detections', []) or []
            if not detections:
                continue
            expressions = sorted({
                d.get('license_expression', 'unknown') for d in detections
            })
            per_file.append((strip_scan_root(entry['path']), expressions))
            for expr in expressions:
                licenses[expr] += 1
    else:
        violations, licenses, per_file = collect_violations(data, policy, refs)

    output = render_summary(mode, licenses, per_file, violations, policy, limit)
    summary_path = os.environ.get('GITHUB_STEP_SUMMARY')
    if summary_path:
        with open(summary_path, 'a') as out:
            out.write(output)
    else:
        sys.stdout.write(output)

    # Annotations surface in the PR "Files changed" view, not just the summary.
    for violation in violations:
        print(violation.annotation(policy))
    return 1 if violations else 0


if __name__ == '__main__':
    sys.exit(main(sys.argv))
