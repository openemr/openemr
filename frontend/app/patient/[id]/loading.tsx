export default function Loading() {
  return (
    <main className="mx-auto flex max-w-5xl flex-col gap-4 p-6">
      <div className="h-16 animate-pulse rounded-md bg-gray-100 dark:bg-gray-800" />
      <div className="grid gap-3 md:grid-cols-2">
        {[0, 1, 2, 3].map((i) => (
          <div
            key={i}
            className="h-24 animate-pulse rounded-md bg-gray-100 dark:bg-gray-800"
          />
        ))}
      </div>
    </main>
  );
}
