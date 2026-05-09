/**
 * Top navigation bar for the signed-in dashboard surface.
 *
 * Mirrors the legacy OpenEMR main_screen.php nav shape (Patients,
 * Calendar, Encounters, Reports, Modules, Admin) so a clinician moving
 * between the two surfaces sees the same vocabulary. Only "Patients"
 * is wired to a real route; the rest are intentionally disabled — the
 * deferred work in PATIENT_DASHBOARD_MIGRATION.md §5 covers porting
 * those modules in a future sprint.
 */
import Link from "next/link";

export type NavSection = "patients" | "calendar" | "encounters" | "reports" | "modules" | "admin";

interface TopNavProps {
  /** Active section (renders with a highlighted underline). */
  active?: NavSection;
  /** OpenEMR `preferred_username` for the user badge. Undefined if unknown. */
  username?: string | null;
}

interface NavItem {
  id: NavSection;
  label: string;
  href: string | null; // null → disabled
  hint?: string;
}

const NAV: NavItem[] = [
  { id: "patients", label: "Patients", href: "/" },
  { id: "calendar", label: "Calendar", href: null, hint: "Coming soon" },
  { id: "encounters", label: "Encounters", href: null, hint: "Coming soon" },
  { id: "reports", label: "Reports", href: null, hint: "Coming soon" },
  { id: "modules", label: "Modules", href: null, hint: "Coming soon" },
  { id: "admin", label: "Admin", href: null, hint: "Coming soon" },
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
          const isDisabled = item.href === null;
          const base = "rounded-md px-3 py-1.5 transition";
          if (isDisabled) {
            return (
              <li key={item.id}>
                <span
                  title={item.hint}
                  aria-disabled="true"
                  className={`${base} cursor-not-allowed text-gray-400 dark:text-gray-600`}
                >
                  {item.label}
                </span>
              </li>
            );
          }
          return (
            <li key={item.id}>
              <Link
                href={item.href ?? "#"}
                className={`${base} ${
                  isActive
                    ? "bg-blue-50 font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                    : "text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                }`}
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
