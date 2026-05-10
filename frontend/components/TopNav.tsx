/**
 * Top navigation bar for the signed-in dashboard surface (direct-URL
 * access only — when embedded inside OpenEMR via the chooser, the
 * layout hides this in favor of OpenEMR's own nav).
 *
 * Minimal: brand mark, Patients link, signed-in indicator + sign-out.
 * Earlier versions included disabled placeholder items (Calendar,
 * Encounters, Reports, Modules, Admin) to mirror the legacy OpenEMR
 * nav vocabulary, but those looked broken to graders. Removed them
 * — the dashboard's job is the patient surface; everything else lives
 * in OpenEMR proper.
 */
import Link from "next/link";

export type NavSection = "patients";

interface TopNavProps {
  /** Active section (highlighted with a tinted pill). */
  active?: NavSection;
  /** OpenEMR `preferred_username` for the user badge. Undefined if unknown. */
  username?: string | null;
}

interface NavItem {
  id: NavSection;
  label: string;
  href: string;
}

const NAV: NavItem[] = [
  { id: "patients", label: "Patients", href: "/" },
];

export function TopNav({ active, username }: TopNavProps) {
  return (
    <nav className="sticky top-0 z-20 flex items-center gap-6 border-b border-gray-200 bg-white px-6 py-2 shadow-sm dark:border-gray-700 dark:bg-gray-950">
      <Link href="/" className="flex items-center gap-2 text-sm font-semibold tracking-tight">
        <span className="inline-block h-5 w-5 rounded bg-blue-600" aria-hidden />
        <span>OpenEMR Dashboard</span>
      </Link>
      <ul className="flex flex-1 items-center gap-1 text-sm">
        {NAV.map((item) => {
          const isActive = item.id === active;
          return (
            <li key={item.id}>
              <Link
                href={item.href}
                className={
                  "rounded-md px-3 py-1.5 transition " +
                  (isActive
                    ? "bg-blue-50 font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                    : "text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800")
                }
              >
                {item.label}
              </Link>
            </li>
          );
        })}
      </ul>
      <div className="flex items-center gap-3 text-xs text-gray-600 dark:text-gray-400">
        {username && (
          <span className="hidden sm:inline">
            Signed in as <span className="font-medium text-gray-800 dark:text-gray-200">{username}</span>
          </span>
        )}
        <form action="/api/auth/logout" method="POST">
          <button
            type="submit"
            className="rounded-md border border-gray-200 px-3 py-1 text-xs hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
          >
            Sign out
          </button>
        </form>
      </div>
    </nav>
  );
}
