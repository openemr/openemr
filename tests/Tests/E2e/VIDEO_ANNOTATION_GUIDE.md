# E2E Test Video Annotation Guide

## Overview

Video recordings of E2E tests are now easier to navigate using visual annotations! The `annotateVideo()` method adds colored banners to the video at key test steps, making it simple to scrub to the right place.

## Quick Start

Add annotations to your tests by calling `$this->annotateVideo()` at key steps:

```php
#[Test]
public function testMyFeature(): void
{
    $this->base();
    try {
        $this->annotateVideo('TEST START: My Feature', 2000, '#4CAF50');

        $this->login(LoginTestData::username, LoginTestData::password);

        $this->annotateVideo('STEP 1: Login complete', 1500);

        // ... rest of test

        $this->annotateVideo('TEST COMPLETE', 2000, '#4CAF50');
    } catch (\Throwable $e) {
        $this->annotateVideo('TEST FAILED: ' . $e->getMessage(), 3000, '#F44336');
        $this->client->quit();
        throw $e;
    }
    $this->client->quit();
}
```

## Method Signature

```php
$this->annotateVideo(
    string $message,           // The text to display
    int $durationMs = 2000,    // How long to show (milliseconds)
    string $backgroundColor = '#FF6B35'  // Banner color
);
```

## Parameters

### `$message` (required)
- The text to display in the banner
- Best practices:
  - Use CAPS for section markers: `'TEST START'`, `'STEP 1'`, `'TEST COMPLETE'`
  - Be concise but descriptive: `'STEP 2: Navigate to Globals'`
  - Include context: `'CHECKPOINT: Form validation passed'`

### `$durationMs` (optional, default: 2000)
- How long the banner stays visible in milliseconds
- Suggested values:
  - **1000ms (1s)**: Quick checkpoints during fast actions
  - **2000ms (2s)**: Standard duration for most steps
  - **3000ms (3s)**: Important milestones or errors
  - **5000ms (5s)**: Critical failures that need attention

### `$backgroundColor` (optional, default: '#FF6B35' orange)
- CSS color value for the banner background
- Suggested color scheme:
  - **Green `#4CAF50`**: Test start/end, success states
  - **Blue `#2196F3`**: Navigation, page loads
  - **Orange `#FF6B35`**: Default, general steps (default)
  - **Purple `#9C27B0`**: User interactions, form fills
  - **Amber `#FF9800`**: Validations, assertions
  - **Red `#F44336`**: Errors, failures

## Common Patterns

### 1. Test Structure Markers
```php
// Start of test
$this->annotateVideo('TEST START: Feature Name', 2000, '#4CAF50');

// End of test
$this->annotateVideo('TEST COMPLETE', 2000, '#4CAF50');

// Test failure
$this->annotateVideo('TEST FAILED: ' . $e->getMessage(), 3000, '#F44336');
```

### 2. Sequential Steps
```php
$this->annotateVideo('STEP 1: Login to application', 2000);
// ... login code ...

$this->annotateVideo('STEP 2: Navigate to Globals page', 2000);
// ... navigation code ...

$this->annotateVideo('STEP 3: Search for setting', 2000);
// ... search code ...

$this->annotateVideo('STEP 4: Save changes', 2000);
// ... save code ...
```

### 3. Checkpoints and Validations
```php
$this->annotateVideo('CHECKPOINT: Verifying form loaded', 1500, '#FF9800');
$this->client->waitFor(XpathsConstantsEditGlobals::GLOBALS_FORM);

$this->annotateVideo('CHECKPOINT: Search results visible', 1500, '#FF9800');
$this->assertGreaterThan(0, count($highlights));
```

### 4. User Interactions
```php
$this->annotateVideo('ACTION: Filling out form fields', 2000, '#9C27B0');
$element->sendKeys('test value');

$this->annotateVideo('ACTION: Clicking Save button', 1500, '#9C27B0');
$this->crawler->filterXPath('//button[@type="submit"]')->click();
```

### 5. Long Operations
```php
$this->annotateVideo('WAITING: Database transaction...', 3000, '#2196F3');
sleep(3);

$this->annotateVideo('COMPLETED: Changes saved', 1500, '#4CAF50');
```

## Best Practices

### DO:
- ✅ Add annotations at the start and end of tests
- ✅ Annotate before major actions (clicks, form submissions)
- ✅ Annotate before assertions/validations
- ✅ Use consistent naming conventions (STEP 1, STEP 2, etc.)
- ✅ Use colors meaningfully (green for success, red for errors)
- ✅ Keep messages concise (under 60 characters)
- ✅ Add failure annotations in catch blocks

### DON'T:
- ❌ Over-annotate (every single line of code)
- ❌ Use very short durations (<1000ms) for important steps
- ❌ Use similar colors for different types of operations
- ❌ Include sensitive data in annotation messages
- ❌ Forget to annotate test failures

## Example: Full Annotated Test

See `tests/Tests/E2e/EXAMPLE_ANNOTATED_TEST.php` for a complete working example.

## Color Reference

| Color Name | Hex Code  | Use Case |
|------------|-----------|----------|
| Green      | `#4CAF50` | Success, test start/end |
| Blue       | `#2196F3` | Navigation, page loads |
| Orange     | `#FF6B35` | Default, general steps |
| Purple     | `#9C27B0` | User interactions |
| Amber      | `#FF9800` | Validations, checkpoints |
| Red        | `#F44336` | Errors, failures |
| Teal       | `#009688` | Database operations |
| Indigo     | `#3F51B5` | API calls |

## How It Works

The `annotateVideo()` method:
1. Injects a JavaScript snippet that creates a fixed banner at the top of the page
2. Styles it with high z-index to appear above all content
3. Auto-removes the banner after the specified duration with a fade-out animation
4. Pauses test execution during the display duration so the banner is captured in the video

## Viewing Annotated Videos

1. Run your E2E tests (videos are recorded automatically in CI)
2. Download the video artifacts from GitHub Actions
3. Open the video in any player
4. Scrub through the video to find your colored annotation banners
5. Each banner marks a specific test step for easy navigation

## Performance Impact

- **Minimal**: Each annotation adds ~2 seconds to test execution time
- Use annotations strategically - you don't need one on every line
- The test still executes normally; annotations only affect video recording
- No impact on test assertions or functionality

## Customization

You can customize the annotation appearance by modifying the `annotateVideo()` method in `tests/Tests/E2e/Base/BaseTrait.php`:

- Change font size (default: 24px)
- Adjust padding (default: 20px)
- Modify z-index (default: 999999)
- Add animations or effects
- Change the fade-out duration

## Troubleshooting

### Annotations not visible
- Ensure you're running tests with video recording enabled
- Check that `COMPOSE_PROFILES: video-recording` is set in CI
- Verify the annotation duration is long enough (>1000ms)

### Annotations blocking UI
- The banner is automatically removed after the duration
- It uses high z-index but shouldn't interfere with test execution
- If needed, adjust the positioning in `BaseTrait.php`

### Too many annotations
- Remove annotations from rapid/repetitive operations
- Use annotations only for major test milestones
- Consider combining related steps into a single annotation

## See Also

- `tests/Tests/E2e/Base/BaseTrait.php` - Implementation
- `tests/Tests/E2e/EXAMPLE_ANNOTATED_TEST.php` - Working example
- `.github/workflows/test.yml` - CI video recording configuration
