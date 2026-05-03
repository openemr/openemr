// copilotProviderFilter — Per-physician scope for the patient finder.
// NOTE: no <?php opener — this fragment is awk-injected mid-file into
// dynamic_finder_ajax.php, which is already inside an open PHP block.
// Injected into stock dynamic_finder_ajax.php just before the COUNT
// queries, so both the count and the listing only return patients
// whose patient_data.providerID matches the logged-in clinician's
// users.id. 'admin' bypasses (sees everything). When authUser is
// missing or the user lookup fails, we fall through unchanged so the
// stock behavior is preserved (e.g. cron, tests).
$copilotAuthUser = $_SESSION['authUser'] ?? '';
if ($copilotAuthUser !== '' && $copilotAuthUser !== 'admin') {
    $copilotUserRow = sqlQuery(
        "SELECT id FROM users WHERE username = ?",
        [$copilotAuthUser]
    );
    if (!empty($copilotUserRow['id'])) {
        $copilotProviderFilter = intval($copilotUserRow['id']);
        $customWhere = "($customWhere) AND providerID = $copilotProviderFilter";
    }
}
