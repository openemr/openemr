import { Subject } from "rxjs";

const subject = new Subject();

export const rxService = {
  sendTimelines: (timeline) => subject.next({ data: timeline }),
  sendPatientData: (patient) => subject.next({ data: patient }),
  send: (message) => subject.next({ text: message }),
  zoomState: (state) => subject.next({ state: state }),
  sendEncounter: (encounter) => subject.next({ encounter: encounter }),
  sendEncounterRequest: (minEncounter) =>
    subject.next({ minEncounter: minEncounter }),
  sendUpdate: (message, data) => subject.next({ text: message, data: data }),
  sendMinRequest: (data, timeline) =>
    subject.next({ min: data, timeline: timeline }),
  clearMessages: () => subject.next(),
  getSubscription: () => subject.asObservable(),
};
