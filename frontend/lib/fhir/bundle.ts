import type { Bundle, Identifier } from "./types";

/** Flatten a Bundle's entries to a typed array, dropping any entry with no resource. */
export function bundleEntries<T>(bundle: Bundle<T> | null | undefined): T[] {
  if (!bundle?.entry) return [];
  const out: T[] = [];
  for (const e of bundle.entry) {
    if (e.resource) out.push(e.resource);
  }
  return out;
}

/**
 * Find the first identifier matching one of the given systems (case-insensitive).
 * Falls back to identifiers whose `type.coding[].code` matches one of the given codes.
 * Returns the value or null.
 */
export function findIdentifier(
  identifiers: Identifier[] | undefined,
  systems: string[],
  fallbackCodes: string[] = [],
): string | null {
  if (!identifiers || identifiers.length === 0) return null;
  const sysLower = systems.map((s) => s.toLowerCase());
  for (const ident of identifiers) {
    if (ident.system && sysLower.includes(ident.system.toLowerCase()) && ident.value) {
      return ident.value;
    }
  }
  if (fallbackCodes.length > 0) {
    const fbLower = fallbackCodes.map((c) => c.toLowerCase());
    for (const ident of identifiers) {
      const codes = ident.type?.coding?.map((c) => c.code?.toLowerCase()) ?? [];
      if (codes.some((c) => c && fbLower.includes(c)) && ident.value) {
        return ident.value;
      }
    }
  }
  return null;
}
