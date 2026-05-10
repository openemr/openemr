import type { PanelDecision } from "./panel-scope";

/**
 * Per-(sessionId, patientId) panel-scope decision cache.
 *
 * Keyed `${sessionId}:${patientId}`. 60-second TTL — acceptable staleness
 * for clinical reassignment in a pilot deployment. The cache is module-
 * scope state for the same reason the token-store is: the proxy route
 * runs in the Node runtime and module state is reachable across
 * requests served by the same process.
 *
 * Extracted to its own module so tests can reset between cases via
 * `__resetForTests()`.
 */

type Entry = { decision: PanelDecision; expiresAt: number };

const cache = new Map<string, Entry>();
const TTL_MS = 60_000;

function key(sessionId: string, patientId: string): string {
  return `${sessionId}:${patientId}`;
}

export function getPanelDecision(
  sessionId: string,
  patientId: string,
): PanelDecision | null {
  const e = cache.get(key(sessionId, patientId));
  if (!e || e.expiresAt <= Date.now()) {
    if (e) cache.delete(key(sessionId, patientId));
    return null;
  }
  return e.decision;
}

export function setPanelDecision(
  sessionId: string,
  patientId: string,
  decision: PanelDecision,
): void {
  cache.set(key(sessionId, patientId), { decision, expiresAt: Date.now() + TTL_MS });
}

export function __resetForTests(): void {
  if (process.env.NODE_ENV !== "test") {
    throw new Error("__resetForTests is test-only");
  }
  cache.clear();
}
