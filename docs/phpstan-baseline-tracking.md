# PHPStan Baseline Tracking

This directory contains tooling to track and visualize the PHPStan baseline size over time.

## Files

| File | Purpose |
|------|---------|
| `phpstan-baseline-chart.html` | Interactive visualization (Chart.js) |
| `phpstan-baseline-history.json` | Historical data, auto-updated by CI |
| `phpstan-baseline-annotations.json` | Manual annotations for notable events |

## How It Works

1. **On every merge to master** that touches `.phpstan/baseline/`, a GitHub Actions workflow extracts the current baseline metrics and appends them to the history file.

2. **The visualization** loads both the history and annotations files to render an interactive chart showing error counts over time.

3. **Annotations** are stored separately so the history can be regenerated without losing manual notes about significant changes.

## Viewing the Chart

### GitHub Pages (recommended)

If GitHub Pages is enabled on the `docs/` folder, the chart is available at:

```
https://openemr.github.io/openemr/phpstan-baseline-chart.html
```

### Local Development

```bash
cd docs
php -S localhost:8000
# Open http://localhost:8000/phpstan-baseline-chart.html
```

### Embedding

Add `?embed` to hide the header/footer for embedding in blog posts:

```html
<iframe src="https://openemr.github.io/openemr/phpstan-baseline-chart.html?embed"
        width="100%" height="500" frameborder="0"></iframe>
```

## Adding Annotations

Edit `phpstan-baseline-annotations.json` to mark significant events:

```json
[
    {
        "commit": "43eabaaf94",
        "label": "PHPStan level 10",
        "color": "#9333ea"
    },
    {
        "commit": "abcd1234",
        "label": "Added new rule"
    }
]
```

| Field | Required | Description |
|-------|----------|-------------|
| `commit` | Yes | Short commit hash (prefix match) |
| `label` | Yes | Text shown on the chart |
| `color` | No | Hex color (default: `#dc2626` red) |
| `position` | No | `start` (top), `center`, or `end` (bottom) |

## Managing History

The `ci/phpstan-baseline-history.php` script has two modes:

### Append (CI use)

Appends the current commit's metrics to the history file:

```bash
php ci/phpstan-baseline-history.php append
```

This is what the GitHub Actions workflow uses.

### Rebuild (manual)

Rebuilds the entire history by iterating through git commits:

```bash
php ci/phpstan-baseline-history.php rebuild [since-commit]
```

The default starting commit is `4b6b18875973ab2cf78dcaad35f910cc5f06171f` (when the baseline format changed to PHP files).

Note: This checks out each commit in sequence, so ensure your working directory is clean (no uncommitted changes to tracked files).

## CI Workflow

The `.github/workflows/phpstan-baseline-metrics.yml` workflow:

- Triggers on pushes to master that modify `.phpstan/baseline/**`
- Runs `php ci/phpstan-baseline-history.php append`
- Commits the updated history with `[skip ci]` to avoid loops
