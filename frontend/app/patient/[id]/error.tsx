"use client";

interface ErrorProps {
  error: Error & { digest?: string };
  reset: () => void;
}

export default function PatientPageError({ error, reset }: ErrorProps) {
  return (
    <main className="mx-auto max-w-3xl p-6">
      <h1 className="text-lg font-semibold">Could not load patient</h1>
      <p className="mt-2 text-sm text-gray-700 dark:text-gray-300">
        {error.message}
        {error.digest ? <span className="ml-2 text-xs text-gray-500">({error.digest})</span> : null}
      </p>
      <button
        type="button"
        onClick={reset}
        className="mt-4 rounded border border-gray-300 px-3 py-1 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
      >
        Retry
      </button>
    </main>
  );
}
