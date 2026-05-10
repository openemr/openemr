import { redirect } from "next/navigation";
import { fhirGet, FhirError } from "@/lib/fhir/client";
import { getSessionUser } from "@/lib/auth/session";
import { PatientHeader } from "@/components/PatientHeader";
import { Allergies } from "@/components/cards/Allergies";
import { Problems } from "@/components/cards/Problems";
import { Medications } from "@/components/cards/Medications";
import { Prescriptions } from "@/components/cards/Prescriptions";
import { CareTeam } from "@/components/cards/CareTeam";
import { Encounters } from "@/components/cards/Encounters";
import { CopilotRail } from "@/components/CopilotRail";
import type { Patient } from "@/lib/fhir/types";

interface PageProps {
  params: Promise<{ id: string }>;
}

export default async function PatientPage({ params }: PageProps) {
  const { id } = await params;
  let patient: Patient;
  try {
    patient = await fhirGet<Patient>(`Patient/${encodeURIComponent(id)}`);
  } catch (err) {
    if (err instanceof FhirError && err.status === 401) {
      // Bounce through OAuth, preserving the requested patient as the
      // post-login destination. This is the path OpenEMR's patient
      // finder hits via dashboard.php — clicking a patient there should
      // not require a manual "Sign in" click.
      redirect(`/api/auth/login?next=${encodeURIComponent(`/patient/${id}`)}`);
    }
    throw err;
  }
  const copilotUrl = process.env.COPILOT_URL ?? "";
  const sessionUser = await getSessionUser();
  return (
    <div className="flex min-h-screen">
      <main className="mx-auto flex max-w-5xl flex-1 flex-col gap-4 p-6">
        <PatientHeader patient={patient} />
        <div className="grid gap-3 md:grid-cols-2">
          <Allergies patientId={id} />
          <Problems patientId={id} />
          <Medications patientId={id} />
          <Prescriptions patientId={id} />
          <CareTeam patientId={id} />
          <Encounters patientId={id} />
        </div>
      </main>
      {copilotUrl && (
        <CopilotRail
          patientId={id}
          copilotUrl={copilotUrl}
          physicianUserId={sessionUser.openemrUsername}
        />
      )}
    </div>
  );
}
