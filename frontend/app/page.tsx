import { cookies } from "next/headers";
import { Suspense } from "react";
import { PatientList } from "@/components/PatientList";
import { StatCards } from "@/components/StatCards";

const SESSION_COOKIE = "dashboard_session";

export default async function Home() {
  const store = await cookies();
  const isLoggedIn = store.has(SESSION_COOKIE);

  if (isLoggedIn) {
    return (
      <main className="mx-auto flex max-w-6xl flex-col gap-6 px-6 py-6">
        <header>
          <h1 className="text-xl font-semibold tracking-tight">Dashboard</h1>
          <p className="mt-0.5 text-sm text-gray-600 dark:text-gray-400">
            Pick a patient below to open their record.
          </p>
        </header>
        <Suspense fallback={<StatCardsSkeleton />}>
          <StatCards />
        </Suspense>
        <Suspense fallback={<PatientListSkeleton />}>
          <PatientList />
        </Suspense>
      </main>
    );
  }

  return (
    <main className="flex min-h-screen flex-col items-center justify-center gap-4 p-8 text-center">
      <h1 className="text-2xl font-semibold">OpenEMR Patient Dashboard</h1>
      <p className="max-w-md text-sm text-gray-600 dark:text-gray-400">
        Sign in with your OpenEMR account to view patient records.
      </p>
      <a
        className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700"
        href="/api/auth/login"
      >
        Sign in with OpenEMR
      </a>
    </main>
  );
}

function StatCardsSkeleton() {
  return (
    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
      {[0, 1, 2].map((i) => (
        <div
          key={i}
          className="h-20 animate-pulse rounded-md border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900"
        />
      ))}
    </div>
  );
}

function PatientListSkeleton() {
  return (
    <div className="h-64 animate-pulse rounded-md border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900" />
  );
}
