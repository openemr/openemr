interface CopilotRailProps {
  patientId: string;
  copilotUrl: string;
  /**
   * OpenEMR username for the signed-in clinician. When provided, it's
   * appended to the iframe URL as `&physician_user_id=...` so the
   * Co-Pilot service can scope the session to this clinician's panel
   * (otherwise Co-Pilot falls back to its COPILOT_ADMIN_USERS bypass).
   */
  physicianUserId?: string | null;
}

/**
 * Embeds the existing W1/W2 Co-Pilot iframe (from copilot/app/web/copilot_iframe.{html,js,css})
 * as a side rail in the patient view. The iframe is hosted on the
 * separate Railway `copilot` service; this component only mounts it
 * with the right query parameters so the agent session opens against
 * the correct patient.
 *
 * Known compromise: physician_user_id query param is omitted because
 * the dashboard's session does not yet expose the OpenEMR username
 * to client code. The Co-Pilot side falls back to its admin-bypass
 * list (COPILOT_ADMIN_USERS env on the copilot service) — sufficient
 * for the demo. Follow-up: decode the OAuth ID token at /api/auth/callback
 * and store the username in the dashboard_session cookie so this
 * component can pass it.
 */
export function CopilotRail({ patientId, copilotUrl, physicianUserId }: CopilotRailProps) {
  const trimmed = copilotUrl.replace(/\/+$/, "");
  const params = new URLSearchParams({ patient_id: patientId });
  if (physicianUserId) {
    params.set("physician_user_id", physicianUserId);
  }
  const src = `${trimmed}/iframe?${params.toString()}`;
  return (
    <aside
      className="hidden w-[400px] shrink-0 border-l border-gray-200 dark:border-gray-700 lg:block"
      aria-label="Clinical Co-Pilot"
    >
      <iframe
        src={src}
        title="Clinical Co-Pilot"
        className="h-screen w-full"
        // Restrict iframe permissions defensively. The Co-Pilot iframe
        // needs scripts and forms (chat input, drag/drop) but not
        // top-navigation or pointer-lock.
        sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox"
      />
    </aside>
  );
}
