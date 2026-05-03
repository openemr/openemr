// copilotProviderFilter — Per-physician scope for the patient finder.
// NOTE: no <?php opener — this fragment is awk-injected mid-file into
// dynamic_finder_ajax.php, which is already inside an open PHP block.
// Injected into stock dynamic_finder_ajax.php just before the COUNT
// queries, so both the count and the listing only return patients
// whose patient_data.providerID matches the logged-in clinician's
// users.id. Admin usernames bypass (see all patients). When authUser
// is missing or the user lookup fails, we fall through unchanged so
// stock behavior is preserved (e.g. cron, tests).
//
// Admin list comes from COPILOT_ADMIN_USERS env (comma-separated).
// Default covers literal 'admin' + EPU/Railway-template auto-admin.
// To onboard a new super-user, append to the env on the openemr
// service — no code change / image rebuild required.
$copilotAuthUser = $_SESSION['authUser'] ?? '';
$copilotAdminList = explode(
    ',',
    getenv('COPILOT_ADMIN_USERS') ?: 'admin,EPU-admin-46'
);
$copilotAdminList = array_map('trim', $copilotAdminList);
if (
    $copilotAuthUser !== ''
    && !in_array($copilotAuthUser, $copilotAdminList, true)
) {
    $copilotUserRow = sqlQuery(
        "SELECT id FROM users WHERE username = ?",
        [$copilotAuthUser]
    );
    if (!empty($copilotUserRow['id'])) {
        $copilotProviderFilter = intval($copilotUserRow['id']);
        $customWhere = "($customWhere) AND providerID = $copilotProviderFilter";
    }
}
