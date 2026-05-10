import type { ReactNode } from "react";

interface CardShellProps {
  title: string;
  count?: number;
  children: ReactNode;
}

export function CardShell({ title, count, children }: CardShellProps) {
  return (
    <section className="rounded-md border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
      <header className="flex items-baseline justify-between border-b border-gray-100 px-4 py-2 dark:border-gray-800">
        <h2 className="text-sm font-semibold tracking-tight">{title}</h2>
        {typeof count === "number" && (
          <span className="text-xs text-gray-500">{count} item{count === 1 ? "" : "s"}</span>
        )}
      </header>
      <div className="p-4 text-sm">{children}</div>
    </section>
  );
}
