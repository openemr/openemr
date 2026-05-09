import { cookies } from "next/headers";

const SESSION_COOKIE = "dashboard_session";

export default async function Home() {
  const store = await cookies();
  const isLoggedIn = store.has(SESSION_COOKIE);
  return (
    <main className="flex min-h-screen flex-col items-center justify-center gap-4 p-8 text-center">
      <h1 className="text-2xl font-semibold">OpenEMR Patient Dashboard</h1>
      {isLoggedIn ? (
        <>
          <p className="max-w-md text-sm text-gray-600 dark:text-gray-400">
            Signed in. Open a patient record to start.
          </p>
          <p className="text-xs text-gray-500">
            Hint: navigate to{" "}
            <code className="rounded bg-gray-100 px-1 py-0.5 dark:bg-gray-800">/patient/&lt;id&gt;</code>
            {" "}with a FHIR Patient UUID.
          </p>
          <form action="/api/auth/logout" method="POST" className="mt-2">
            <button
              type="submit"
              className="text-xs text-gray-500 underline hover:text-gray-700"
            >
              Sign out
            </button>
          </form>
        </>
      ) : (
        <>
          <p className="max-w-md text-sm text-gray-600 dark:text-gray-400">
            Sign in with your OpenEMR account to view patient records.
          </p>
          <a
            className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700"
            href="/api/auth/login"
          >
            Sign in with OpenEMR
          </a>
        </>
      )}
    </main>
  );
}
