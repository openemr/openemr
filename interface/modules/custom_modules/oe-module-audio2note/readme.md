# OpenEMR Audio2Note Integration

## OpenEMR Developer Access

For OpenEMR developers wishing to contribute to or test this module, development license keys and API credentials can be requested by emailing audio2note@shem.slmail.me.

## Project Overview
This project addresses the need for healthcare professionals using OpenEMR to efficiently process audio recordings from patient encounters and integrate the resulting clinical documentation (transcription, H&P, SOAP Note, billing) directly into the patient's electronic health record, specifically within the SOAP note. This reduces manual data entry, saves time, and improves the accuracy and completeness of patient records. Though its current iteration is targeted at outpatient and inpatient internal medicine and its subspecialties, its breadth can be expanded based on user demand.

## Privacy Features

-   **Local LLM Processing:** This module is designed to integrate with backend services that can leverage locally hosted Large Language Models (LLMs) for transcription and note generation, offering greater control over data processing.
-   **Privacy-Focused Design:** Audio files are streamed directly to the backend processing service and are not stored at rest on the OpenEMR server. The backend service is designed to delete audio files immediately after processing, thereby conserving clinic resources and enhancing patient privacy.
## How it Works
The integration should provide a seamless workflow within the OpenEMR patient encounter interface. Users should be able to easily upload an audio file related to the current encounter. The system should then automatically send this audio to the backend server for processing. Upon receiving the processed data (transcription, HNP, billing), the system should automatically populate the corresponding sections of the patient's SOAP note for that encounter.

### Data Security: Encryption in Transit

All data transmission involving sensitive patient audio files is secured using HTTPS. This includes:
*   **User Browser to OpenEMR Server:** The initial audio file upload from the user's browser to the OpenEMR server is secured by the OpenEMR web server's HTTPS configuration.
*   **OpenEMR Module to Backend Service:** Communication from the OpenEMR module to the `backendAudioProcess` service (for both initiating transcription and polling for results) is enforced to use HTTPS. The Guzzle HTTP client used for these calls verifies SSL certificates by default, ensuring secure communication.
*   **Backend Service Internal Communications:** Internal communications within the backend Audio Process service (e.g., between microservices, to its database) also utilize secure, encrypted channels.

## User Experience Goals
- **Ease of Use:** The audio upload and transcription triggering process should be intuitive and require minimal steps within the OpenEMR interface.
- **Efficiency:** The integration should significantly reduce the time and effort required to document audio-recorded encounters compared to manual transcription and data entry.
- **Accuracy:** By directly integrating the processed output, the risk of transcription errors or data entry mistakes is minimized.
- **Contextual Relevance:** The functionality should be available within the patient encounter context, ensuring the processed data is linked to the correct patient and visit.
- **Clear Feedback:** Users should receive clear indications of the process status (uploading, processing, success, errors).

# Licensing
# How it Should Work (Licensing Aspect)
1.  **Purchase:** An administrator of an OpenEMR instance purchases a subscription for the "Audio to Note" service from the website. Upon successful purchase, they receive a unique license key, Consumer Key & Secret.
2.  **Configuration in OpenEMR:** The OpenEMR administrator navigates to the `Audio2Note` module's settings page. They input the received license key and the API credentials (Consumer Key & Secret).
3.  **Activation & Instance Linking:** When the settings are saved, the OpenEMR module communicates with the website license manager API.
4.  **Usage Control:**
    *   Before any user attempts to use the Audio2Note feature, the OpenEMR module checks the status of its configured license key.
    *   If the license key is valid (active, not expired, and correctly associated with the instance), the Audio2Note functionality proceeds.
    *   If the license is invalid, expired, or fails validation, the feature is disabled, and the user is informed.
5.  **Key Change:** If the administrator changes the license key in OpenEMR, the module activates the new key and then attempts to deactivate the old key's specific activation.

## User Experience Goals (Licensing Aspect)
-   **Admin Experience:**
    -   **Simple Setup:** Easy and intuitive process for administrators to enter and save license key and API credentials.
    -   **Immediate Feedback:** Clear indication of whether the entered license key was successfully activated/validated upon saving settings.
    -   **Clear Error Messages:** Understandable error messages if activation/validation fails (e.g., key invalid, API connection error).
-   **End-User Experience (Clinical Users):**
    -   **Seamless for Valid Licenses:** If the license is valid, the Audio2Note feature should work without any interruption or awareness of the licensing mechanism.
    -   **Informative Blocking:** If the license is invalid/expired, users attempting to use the feature should receive a clear, non-technical message explaining why the service is unavailable.
-   **Security & Trust:**
    -   License key and API credentials are encrypted using industry standards on client and server.
-   
# Planned Development
## The module had a modular design. This means that addtional features can be added to it with relative ease. Currently, we are working on:
1. Integration of Audio2Note output into additional forms (vital signs, ICD10 coding, and so on)
2. Transcript-only function (in case providers prefer to dictate their note)
3. reviewing and summarzing past notes in newly created SOAP notes
4. Expanding to other subspecialties (based on demand and availability of high quality data sets)
5. and more....
