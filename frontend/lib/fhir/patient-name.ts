import type { HumanName } from "./types";

/**
 * Render a FHIR HumanName[] as a single display string.
 *
 * Preference order:
 * 1. Names with `use === "official"` win over others.
 * 2. Within the chosen name, prefer `text` if present.
 * 3. Else compose `${prefix} ${given} ${family} ${suffix}`, trimmed.
 *
 * Returns "(no name)" if nothing usable is found.
 */
export function renderName(names: HumanName[] | undefined): string {
  if (!names || names.length === 0) return "(no name)";
  const official = names.find((n) => n.use === "official");
  const chosen = official ?? names[0];
  if (chosen.text && chosen.text.trim().length > 0) {
    return chosen.text.trim();
  }
  const parts: string[] = [];
  if (chosen.prefix?.length) parts.push(chosen.prefix.join(" "));
  if (chosen.given?.length) parts.push(chosen.given.join(" "));
  if (chosen.family) parts.push(chosen.family);
  if (chosen.suffix?.length) parts.push(chosen.suffix.join(" "));
  const composed = parts.join(" ").replace(/\s+/g, " ").trim();
  return composed.length > 0 ? composed : "(no name)";
}
