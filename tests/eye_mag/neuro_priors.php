<?php

/** Actual Neuro PRIORS renderer with an isolated, read-only historical record. */
function sourceFunction(string $source, string $name): string
{
    $tokens = token_get_all($source);
    for ($index = 0; $index < count($tokens); ++$index) {
        if (!is_array($tokens[$index]) || $tokens[$index][0] !== T_FUNCTION) {
            continue;
        }
        $cursor = $index + 1;
        while (is_array($tokens[$cursor]) && $tokens[$cursor][0] === T_WHITESPACE) {
            ++$cursor;
        }
        if (!is_array($tokens[$cursor]) || $tokens[$cursor][1] !== $name) {
            continue;
        }
        $body = '';
        $depth = 0;
        $started = false;
        for (; $index < count($tokens); ++$index) {
            $token = $tokens[$index];
            $body .= is_array($token) ? $token[1] : $token;
            if ($token === '{' || (is_array($token) && in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                ++$depth;
                $started = true;
            } elseif ($token === '}' && --$depth === 0 && $started) {
                return $body;
            }
        }
    }
    throw new RuntimeException('Function missing: ' . $name);
}

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function attr($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function text($value): string
{
    return attr($value);
}

function xlt($value): string
{
    return attr(preg_replace('/\{\{.*?\}\}/', '', $value));
}

class SessionWrapperFactory
{
    public static function getInstance(): self { return new self(); }
    public function getActiveSession(): self { return $this; }
    public function get($name): int { return 99; }
}

function sqlStatement($query, $parameters = [])
{
    check(str_starts_with(ltrim($query), 'SELECT'), 'PRIORS must not write data');
    $GLOBALS['queries'][] = [$query, $parameters];
    return new stdClass();
}

function sqlFetchArray($result) { return false; }

function sqlQuery($query, $parameters = []): array
{
    check(str_starts_with(ltrim($query), 'SELECT'), 'PRIORS must not write data');
    $GLOBALS['queries'][] = [$query, $parameters];
    if (str_contains($query, 'form_eye_base.pid')) {
        check($parameters === [42, 17], 'Historical exam remains patient scoped');
        return $GLOBALS['exam'];
    }
    if (str_contains($query, 'Eye_Motility_Display')) {
        return ['option_id' => $GLOBALS['display']];
    }
    throw new RuntimeException('Unexpected query');
}

function priors_select($zone, $original, $prior, $patient): string
{
    check([$zone, $original, $prior, $patient] === ['NEURO', 20, 17, 42], 'Selector receives the existing exam identity');
    return '<span data-prior-selector="17">Copy / previous / next</span>';
}

$root = dirname(__DIR__, 2);
$source = file_get_contents($root . '/interface/forms/eye_mag/php/eye_mag_functions.php');
eval(sourceFunction($source, 'eye_mag_neuro_prior'));
eval(sourceFunction($source, 'display_PRIOR_section'));
$form_folder = 'eye_mag';
$queries = [];
$display = 'H';
$exam = [
    'STEREO_FLY' => 'Yes', 'STEREO_ANIMALS' => '2', 'STEREO_CIRCLES' => '7', 'STEREO_FIXATION_PREF' => 'OS',
    'STEREOPSIS' => 'Older test result', 'ACT' => 'on', 'MOTILITYNORMAL' => '0',
    'ODCOLOR' => '11/11', 'OSCOLOR' => '10/11', 'ODREDDESAT' => '100', 'OSREDDESAT' => '75',
    'ODCOINS' => '1.00', 'OSCOINS' => '0.75', 'ODNPA' => '8', 'OSNPA' => '9', 'NPC' => '6 cm',
    'DACCDIST' => '10', 'DACCNEAR' => '12', 'CACCDIST' => '14', 'CACCNEAR' => '16', 'VERTFUSAMPS' => '3',
    'MOTILITY_RR' => '2', 'MOTILITY_LLIO' => '3', 'NEURO_COMMENTS' => '<script>historical & unchanged</script>',
];
foreach (['SCDIST', 'CCDIST', 'SCNEAR', 'CCNEAR'] as $view) {
    for ($position = 1; $position <= 11; ++$position) {
        $exam['ACT' . $position . $view] = $view . '-' . $position;
    }
}
$exam['ACT5SCDIST'] = "2 XT\n1 RHT";
$before = $exam;
$html = display_PRIOR_section('NEURO', 20, 17, 42);
if (in_array('--html', $argv, true)) {
    echo '<!doctype html><html><head><meta charset="utf-8"><style>body{font:14px Arial;margin:12px} .fixture{width:560px;max-width:100%;box-sizing:border-box;padding:8px;border:1px solid #aaa} input,textarea{background:#d3cdf7;border:1px solid #aaa;border-radius:3px;font:inherit} input[type=text]{height:22px} .borderShadow{background:#fff;box-shadow:2px 2px 5px #aaa}</style><style>';
    echo file_get_contents($root . '/interface/forms/eye_mag/css/style.css');
    echo '</style></head><body><div class="fixture PRIORS_class PRIORS">' . $html . '</div></body></html>';
    exit;
}
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);
check($xpath->query('//*[@data-prior-selector="17"]')->length === 1, 'Existing date/copy navigation remains');
check($xpath->query('//svg[@data-display="H"]')->length === 2, 'Both charts use the current H preference');
check($xpath->query('//svg/g[@data-field="MOTILITY_RR"]/*')->length === 2, 'Right-eye lateral grade retains two marks');
check($xpath->query('//svg/g[@data-field="MOTILITY_LLIO"]/*')->length === 3, 'Left-eye oblique grade retains three marks');
check($xpath->query('//*[@id="PRIOR_STEREO_ANIMALS" and @value="2"]')->length === 1, 'New stereo animal value is preserved');
check($xpath->query('//*[@id="PRIOR_STEREO_CIRCLES" and @value="7"]')->length === 1, 'New stereo circle value is preserved');
check($xpath->query('//*[contains(@class,"eye-neuro-prior-fixation")]//*[contains(@class,"is-selected") and text()="OS"]')->length === 1, 'Stored fixation preference is shown');
check($xpath->query('//*[@id="PRIOR_STEREOPSIS" and @value="Older test result"]')->length === 1, 'Legacy stereo findings remain visible');
foreach ($exam as $field => $value) {
    if (str_starts_with($field, 'MOTILITY_') || in_array($field, ['STEREO_FLY', 'STEREO_FIXATION_PREF', 'ACT', 'MOTILITYNORMAL', 'NEURO_COMMENTS'], true)) {
        continue;
    }
    $node = $xpath->query('//*[@id="PRIOR_' . $field . '"]')->item(0);
    check($node !== null && ($node->tagName === 'textarea' ? $node->textContent : $node->getAttribute('value')) === $value, 'Historical field preserved: ' . $field);
}
check($xpath->query('//input[not(@disabled)]|//textarea[not(@disabled)]')->length === 0, 'Every historical field is disabled');
check($xpath->query('//script')->length === 0, 'Historical text cannot become executable markup');
check($xpath->query('//*[@id="PRIOR_NEURO_COMMENTS"]')->item(0)->textContent === $exam['NEURO_COMMENTS'], 'Comments retain literal historical content');
check($exam === $before, 'Rendering does not alter historical findings');
check(count($queries) === 3, 'Prior rendering only reads preferences, patient-scoped findings and chart style');
$display = 'X';
$exam['MOTILITY_RS'] = '4';
$x = display_PRIOR_section('NEURO', 20, 17, 42);
$dom = new DOMDocument();
@$dom->loadHTML($x);
$xpath = new DOMXPath($dom);
check($xpath->query('//svg[@data-display="X"]')->length === 2, 'Both charts use the current X preference');
check($xpath->query('//svg/g[@data-field="MOTILITY_RS"]/*')->length === 4, 'X view retains all four superior-ray marks');
$exam = [];
$empty = display_PRIOR_section('NEURO', 20, 17, 42);
check(!str_contains($empty, 'checked'), 'Absent historical values are not inferred as normal');
$exam = ['STEREO_FLY' => 'U/A'];
check(str_contains(display_PRIOR_section('NEURO', 20, 17, 42), 'type="checkbox" checked'), 'Unable is preserved for historical stereo');
echo "Neuro PRIORS: historical fields, X/H charts, scoping, read-only output and escaping pass.\n";
