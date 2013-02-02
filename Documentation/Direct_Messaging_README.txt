Direct Messaging with OpenEMR and EMR Direct phiMail(TM)
Version 1.0, 1 Feb 2013

Purpose:
To provide a secure method from within OpenEMR for sending protected health 
information to another Direct address using the Direct Project messaging 
standard, as a first step toward the goal of satisfying the three MU2 criteria 
requiring the use of Direct messaging.  (For general information about Direct messaging, 
see http://www.emrdirect.com/about-directed-exchange-and-secure-direct-messaging.html)

IMPORTANT:
EMR Direct currently considers the OpenEMR Direct Messaging features to be in test-mode 
and not ready for use with real PHI. Some known limitations include: 
a. the current code does not have the ability to query for delayed failure or final delivery 
notifications from the phiMail server, and 
b. the current code only supports a single shared "group" Direct Address for each OpenEMR 
installation.

Problems Solved:
1. Patient initiated transmission of CCDA data from the Report section of the Patient Portal 
interface.
2. Provider initiated transmission of CCDA data from the Report section of the Patient pane
in the main OpenEMR interface.
3. Log all CCDA data transmissions including date/time, patient, and whether transmission 
was initiated by the patient through the Patient Portal or by an OpenEMR user through the 
main interface.

How it Works:
Once configured, OpenEMR will interface with a phiMail Direct messaging server to complete the
required message transactions. The phiMail platform is described on the EMR Direct website, 
http://www.emrdirect.com and http://www.emrdirect.com/phimail-faq.html.

What you need before enabling Direct Messaging in OpenEMR:
1. Test-Mode: Developers may contact EMR Direct to request complimentary test address and 
digital certificate provisioning, as well as API documentation, at support@emrdirect.com. 
2. Production-Mode: Healthcare provider users should begin by signing up for a Direct message 
account with EMR Direct. 
Subscribers will receive the username, password, and server address information with which to 
configure OpenEMR. Applicants may visit http://www.emrdirect.com/subscribe.html to begin the
process. 

How to enable the Direct Messaging Features in OpenEMR:
Setup of phimail Direct messaging Service is done in the Administration::Globals::Connectors tab
1. Check the "Enable phiMail Direct Messaging Service" checkbox.
2. Enter the Server Address, Username, and Password provided to you by EMR Direct.
3. Click the "Save" button.

How to use the Direct Messaging Features in OpenEMR:
When the phiMail Direct Messaging service is enabled, an additional "Transmit" button will
appear in the Continuity of Care Document (CCD) section of the Reports section in both the
Patient Portal and the Patient pane of the main provider interface. 

To transmit a CCD, first click the "Transmit" button. This will open a small dialog immediately 
below the button with a form field to enter the intended recipient's Direct Address. Clicking
"Transmit" again will hide the dialog.

A Direct Address should have the same form as a regular email address, e.g. 
jonesclinic@direct.example.com. Enter the address in the field and click the "Send" button 
immediately to the right of the field. Only a single recipient may be specified in the field.
The Send button will be temporarily disabled while OpenEMR is communicating with the phiMail server.
This will only work for properly-configured Direct addresses.   Attempts to send to a regular 
email address or Direct address outside of our test mode "trust sandbox" will fail.

OpenEMR will then display a status message immediately below the Address field, the 
success or failure of the message transmission, or an error message. If the message is
successfully submitted to the server, the Address field will be cleared to prevent accidental
re-transmission. If multiple recipients are required, the next recipient can now be entered.


Trademark Notice: phiMail is a trademark of EMR Direct.

Copyright (c) 2013 EMR Direct.
