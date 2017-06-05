# OpenEMR Quick Reference Handout

This handout is a reference source to many basic OpenEMR activities, with pointers to more information for further study. It is not
atutorial in itself, but is intended as a reminder sheet for what was learned in the more comprehensive tutorials and online videos.
If printed out as a pdf, it can be viewed in the same terminal as OpenEMR; links to materials are given as external links for use in
that scenario.

## Sources

* Much of the handout is abbreviated content from the [OpenEMR User Guide](http://open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#User_Manuals)
* The first half of this handout closely reflects the first three pages of the OpenEMR v4.2 User Guide:
    - [Getting Started](getting_started.html)
    - [Main Screen & Navigation](main_screen_nav.html)
    - [Setting Up Your Clinic](setup_your_clinic.html)
* In those User Guides (or Manuals) the Supplementary Topics mentioned
below are listed underneath the Main Topics.
* While learning the OpenEMR interface, consider printing out the (illustrated) 4.2 User Guide section, "Main Screen & Navigation" to keep by your OpenEMR terminals. The few differences between it and the 4.1.2 version may be manually corrected.
* PLEASE NOTE: these instructions apply to a standard/ stock installation of OpenEMR. Any system customization may modify features and capabilities.
-HTuck/MI-Squared

## Navigation Principles
* The majority of OpenEMR's functions are initiated through the left navigation ("nav") menu
* Clicking on the nav menu item displays the interface (screen, panel or dialog) for that activity in either the top or bottom panel of the main OpenEMR display on the right side of the screen.
* Each menu item is either a distinct task by itself OR is the entrance point to a collection of related tasks. For example:

The '''Administration-\> ACL '''menu item displays a single panel from
which the only thing you do is set users' access permissions.

The '''Administration-\> Globals '''menu item displays a single panel
but it has multiple tabs with settings for many different functions.

## Navigation Menu

### Calendar
Shows the Providers' schedule.

See See 4.2 User Guide Supplementary Topic: "Using the Calendar" and "Calendar Categories: How to Change Names, Colors and Intervals of Appointments"

### Messages
Re-display the logged- in user's message and Reminder Center (visible on login)

### Patient / Client
Patient- related activities: search for a patient; add a new patient; create visit (encounter) form; access different clinical forms for a visit.

Custom clinical forms will appear in the sub menus of this nav menu item.

For step- by- step how to build your own special purpose data collection forms,

See 4.2 User Guide Supplementary Topic: "Sample Layout Based Visit Form Tutorial" and "Sample NationNotes Form"

### Fees
Access the Fee Sheet (one way); receive patient payments, register insurance EOB and EDI payments;

### Inventory
If your clinic sells meds or supplies, maintain that inventory here (not activated by default)

See 4.2 User Guide Supplementary Topic: "Pharmacy Dispensary"

### Procedures
Setup and use the order entry procedures, if configured.

See 4.2 User Guide Supplementary Topic: Procedures Module Configuration for Manual Result Entry

### Administration
Managing the OpenEMR environment. Mostly for System Admin personnel

***Selected items:***

#### Global
Configure most aspects of the OpenEMR environment.

See 4.2 User Guide Supplementary Topic: "Admin Globals Summary"

#### Facilities
Configure the practice's facility identifiers

#### Users
Add new user accounts or modify existing ones

#### ACL
Assign users their access privileges to aspects of OpenEMR See See 4.2 User Guide Supplementary Topic: "Access Controls"

#### Backup
See See 4.2 User Guide Supplementary Topic: "Backup Tools"

#### Rules / Alerts / Patient Reminders
Aspects of configuring and using the Clinical Decision Rules engine. See See 4.2 User Guide Supplementary Topic: "Patient Reminders"

Reports
-----------
Generate multiple financial, clinical and MU reporting documents

Miscellaneous
---------------------
Sundry settings or functions that don't fit elsewhere ***notably:***

### Preferences
Settings '''each user '''may customize to their own preferences See 4.2 User Guide Supplementary Topic: "User Settings"

Common Workflows
================

Add a New Patient
----------------------------
See 4.2 User Guide Main Topic: "Adding a New Patient"

1. Nav menu: **Patient / Client**
2. **New / Search**
3. Minimum required data:
    * First name (first text area)
    * Last name (third text area)
    * DOB
    * Sex
4. Click button at bottom **'Create New Patient**'
5. At bottom of popup, click button, **'Confirm Create New Patient**'

Using the Calendar/ Create Appointment
-----------------------------------------------------------
See 4.2 User Guide Main Topic: "Using The Calendar"

Also Supplemental Topics: Calendar Categories: How to Change Names, Colors and Intervals of Appointments.

Make an appointment:
1. Nav Menu: **Calendar**
2. In Provider List under calendar at screen left, select provider
3. Click in schedule on desired time
4. In popup "Add New Event" select appointment category, select patient, provider, time, duration. (if time not desirable, click button **'Find Available**' and select from 2nd popup)
5. Enter any comments; these will appear to provider in beginning of encounter form
6. Click **Save** button

Check Pt In/ Track Appointment Status
--------------------------------------------------------
> Any staff with calendar privileges may change appointment status

1. At Calendar Screen, click on the appointment time in the Pt's schedule slot
2. In **'Edit Event**' panel select from **'Status**' dropdown menu the appropriate status
3. Click **'Save**' button at popup bottom

Start / Conduct Encounter
-------------------------
See 4.2 User Guide Main Topic: "New Encounters & Coding"

1. Click on Pt name in calendar to begin encounter
    > If new encounter screen does not appear click nav menu '''Patient/ Client -\> Visits -\> Create Visit '''
2. Enter **Consultation Brief Description**
3. Select if not already present,  **Visit Category, Sensitivity, Date of Service**
4. Click issues associated with this encounter if present (right hand issues list)
5. Click **Save** button at top
6. New encounter summary panel appears; proceed with encounter using forms from shaded menu bars at top of encounter summary panel.
    * Encounter Summary -- returns to this summary screen
    * Administrative - quick access to modules in nav menu:
    * Fee Sheet - also accessible from *Admin-\> FEES*
    * Misc Billing Options HCFA - from *Patient/ Client-\> Visit Forms*
    * New Encounter Form - also from *Patient/ Client-\> Visits, Create Visit*
    * Procedure Order - also from *Patient/ Client-\> Visit Forms*
    * Clinical - also from *Patient/ Client-\> Visit Forms*
7. Click **'Save**' buttons at top or bottom of all forms
8. Add **Medications**, new **Problems, Issues**, etc in patient summary screen
9. If new Issues were introduced, click **Issues** link in patient summary screen and associate Issue with encounter
10.  Return to Calendar and select next patient

Fee Sheet
---------------

1.  In the Encounter summary screen select Fee Sheet from menu bar (also accessible from Admin-\> FEES)
2. First, select at panel bottom, the price level if not Standard
3. Select CPT4 codes from service category dropdowns
4. **'Search**' – select diagnosis and procedure codeset; enter partial search term;
5. Click **'Search**' button
6. Click on search result to add to fee sheet
7. Select Diagnoses from search tools
8. Itemize inventory products and their prices dispensed from **'Products**' drop down menu
9. **'Review**' button - survey fee summary to this point
10. **'Add Copay**' button - to fees, if applicable
11. Add item **'Modifiers**', **'Price**', **'Units**', select **'Justification**'
12. Select from dropdown menu, **'Rendering Provider**'
13. **'Refresh**' display if multiple changes have been made
14. Click **'Save**' button, or **'Cancel**'

Add Patient Issues
------------------
See 4.2 User Guide Main Topic: "Issues & Immunizations"

1. In Pt summary screen click on link at top, "Issues'
2. Click on relevant Issue or click **'Add**' button in desired issue category
3. Select issue title (text to be displayed) or enter other title
4. Search for/ select diagnosis code
5. Enter other data
6. Click **'Save**' button on popup
7. Click small button (with zero in it) at right in “Enc” column
8. Click encounter in right half of new popup with today's date in it
9. Click **'Save**' button on popup

Add Prescription/ Order Medication
----------------------------------------------------
***(for OpenEMR, not NewCrop eRx)***

1. Click **'Edit**' button for **'Prescription**' at bottom right of pt summary screen
2. Click **'Add**' button
3. Provide info as appropriate
4. **“Drug”**: to select drug name, click text "**(click here to search)**"
5. Remember:
    * "**Quantity**" = dispense quantity
    * "**Medicine Units**" = is strength, eg 200, then select units from dropdown menu
    * "**Take**" = number per dose;
    * Select from dropdown menus: **form, route, frequency**
    * "**Refills**" = how many the Rx is good for
    * "**\# of tablets**" = number to dispense in each refill (even if not tablets)
    * "**Notes**" = will appear on the Rx
    * "**Add to med list**": select **'yes**' and this med will appear in the issues list.
6.  Click oval **'Save**' button at top
7.  To print a Rx, click **'List**' button at top of Prescriptions panel, and select type of printout for the desired prescription .

Add Note About Patient for Other Staff
----------------------------------------------------------
1.  In Pt summary screen next to **Notes** item click on **'(expand)**' link to view all current notes
2.  Click **Edit** button next to **Notes** item to invoke Patient Notes screen
3.  Click **'Add**' button at top
4.  Select from ''''Type' '''dropdown menu the type of note; i.e. what sort of concern it refers to
5.  Select from **'To:**' dropdown menu the recipient of the note
6.  Enter note in text area
7.  Click button **'Save As New Note**'
    * Note will appear in patient's summary page and recipient's **Messages and Reminders** on their next login

Create Referrals/ Transactions
--------------------------------------------
1.  At top of Pt summary screen click link, **'Transactions**'
2.  On Patient Transactions panel click **'Add**' button at top
3.  Set **Referral date, Refer By**
4.  Select **External Referral**: Yes/ no
5.  Select **Refer To**:
    * if 'External Referral' = no, displays in-house providers
    * if 'External Referral' = yes, displays names of all external entities entered into the facility's Address Book.
6. Enter **Reason**
7. **Referrer Diagnosis**: free text
8. Select **Risk Level**
9. **Requested Service**: is Service Code search dialog
10. Click on appropriate search return
12. Select **Include Vitals**? Yes/ No
13. At top right, check or not: **Sent Medical Records**
14. Click **'Save**' button at top of panel
15. At **Patient Transaction** panel click oval **'PRINT**' button at left
16. Click black **X** to exit Patient Transaction panel

Document Immunizations
-------------------------------------
On patient Summary Screen, at bottom of right-hand issues list, locate item **'Immunizations**'

1.  Click **'Edit**' button at left
2.  Click in **'Immunization (CVX code)**' text area
3.  In search popup enter partial vaccine name in **'Search for**' text area
4.  Click **'Search**' button
5.  Click on desired **“Code Description”**in search results
6.  Description is transferred to “'''Immunizations” '''display
7.  Enter remaining information
8.  Click **Save** at bottom

Basic Billing
-------------
For detailed steps please see [Supplimentary Topics, Basic Billing](http://open-emr.org/wiki/index.php/Basic_Billing_4.1)

* OpenEMR's Billing and Batch Payments modules are structured so their workflows may be performed separately, in any order.
* Billing and Batch Payments functions are restricted to the Admin user and any others with the *Accounting* ACL access permission.
* You do NOT need to be logged into the patient you're doing the billing work on.
* Nav menu: **'Fees-\> Billing**': reports the claims from fee sheets that are ready to be corrected and billed.
* Nav menu: **'Fees-\> Batch Payments**':
    - **New Payments** tab:
        - Log in an EOB / payment on patients' accounts may allocate funds to pts' accounts at that time or later
    - **Search Payment** tab:
        - Find payments ready to be allocated, and allocate them
    - **ERA Posting** tab:
        - Process an incoming ERA file.

Generate Claims
---------------
For detailed steps please see [Supplimentary Topics, Basic Billing](http://open-emr.org/wiki/index.php/Basic_Billing_4.1)

* Nav menu: **Fees-\> Billing**
* Billing Manager module: on opening, by default it displays unbilled encounters from current date ("today").
* Use the two-panel **'Choose Criteria/ Current Criteria**' search tool to display encounters matching the criteria
* To delete existing **“Current Criteria”** in right window:
    1. Click criteria
    2. Click red X

Generate claims:
1. Choose criteria in left window
2. Enter specific criteria parameters in middle space. Criteria appear in right window
3. Click text **\[Update List\]** at top right
4. In lower panel results display click links:
    * **[To Enctr (date)]** : display that encounter to correct encounter (Fee Sheet) errors
    * **[To Dems]** : display that patient's Demographics to correct Demographics errors
    * **(Expand)** : show billing status message on encounter; also to locate previous x12 batch file
5. Click in checkbox at right end of all encounters for which a claim will be made
6. Click button **'Generate X12**' to generate claim file
    * Popup reminds to check the log after producing x12 batch file
7. Click **'OK**' to make x12 batch file and mark item as cleared
    * Click 'Cancel' to generate x12 and NOT mark claim as cleared
8. **'Save File**' dialog appears
9. Click **'OK**' to save the batch file to browser's Download directory/ folder
10. Click link at top right, **View Log**
11. View error log file
12. Reopen the claim with errors in it (see below) and correct errors
13. Re-submit the claim: repeat from Step 5 above including 'View Log')
14. Process next claim.

Reopen a Billed Encounter
---------------------------------------
* In Billing Manager module:
* If error log indicates an error condition in the encounter, the encounter must be re-opened:
    1.  Search for encounter again (billing status = "Billed" or "All")
    2.  Click link **'Update List**'
    3.  Select checkbox at right of faulty encounter
* (activates the **'Re-Open**' button )
    1.  Click **'Re-Open**' button
    2.  Read notification of re-opening; click link **'back**'

Generate HCFA Forms
----------------------------------
Nav menu: **'Fees-\> Billing Manager**'

1.  Remove search criteria in right window
2.  Choose criteria, "Billing Status"; parameter: "All"
3.  Enter (or re-enter) date range
4.  Click **'Update List'**
5.  Click in checkbox at right of encounter
6.  Click button to generate desired form

Batch Processing EOB payments and Accounts Receivables
-------------------------------------------------------------------------------------
* Nav menu: **Fees --\> Batch Payments**
* Select task from the 3 tabs:
    - **New Payment**: record received EOBs/ payments on account;
    - **Search Payment**: find EOB's/ payments that have not been (fully) allocated
    - **ERA Posting**: process an ERA file

Log EOB/ Payment
-----------------------------
On receipt of an EOB and payment from a payor:

1. Select **'New Payment**' tab reveals **"Batch Payment Entry"**
2. Enter information from the EOB:
    * Date: of EOB
    * Post to Date: payment posting date
    * Payment Method: defaults to "Check Payment"
    * Check Number: If Payment Method is "Check", enter EOB payment check number
    * Payment amount: Total amount of EOB payment
    * Paying Entity: Insurance or Patient
    * Payment Category: Family/ Group/ Insurance/ Patient/ Pre-payment
    * Payment From: payor name; enter partial name and select fullname from popup
    * Deposit Date: the payment is to be deposited in facility's account
    * Description: (free text field) description of the payment; optional
    * Undistributed: red text area at right is amount of total payment yet undistributed decreases as allocations made to patients' accounts.
3. Finish logging EOB/ payment:
4. If allocating these funds later:
    *   Click button **'Save Changes**' and answer **'Yes**' to popup
    *   Click on the **'New Payment**' tab again and log another EOB
5. If allocating funds now:
     * Click button, **'Allocate**' and answer **'Yes**' to popup

Allocate EOB Payment
--------------------
1. In new panel below Batch Payment Entry panel, enter Patient's partial name as given in EOB
2. Select charge type:
    * **"Non-paid"**: charges which have not had any payments applied yet
    * **"Show Primary Complete"**: Primary Insurance has been paid
    * **"Show All Transactions"**: all, of whatever status.
3. New lower panel shows all unresolved charges for this patient from this payer
    * Note left column, **"Post For"** gives the next payer due to pay.
    * If all insurances have paid, this displays*' "Pat"*' (Patient)
    * **"Remainder"** column lists amount for that service that remains to be paid
4. Enter in **"Payment"** column the EOB/ amounts for each of this patient's services
5. Click in other text area to calculate amounts
6. When finished, if EOB has no payments for other patients
7. Click button **'Finish Payments**'
8. Enter next patient's name in new panel OR
9. Repeat from Step \#4 entering next patient name from EOB until all payments are allocated
10. Click button ''''Post Payments' '''
    * To enter and allocate more EOB's, click tab **'New Payment**' and repeat steps from \#1 above

Allocate payments of previously logged, unallocated EOB
----------------------------------------------------------------------------------
1. On Nav menu: **Fees-\> Batch Payments**, tab: **Search Payment**
    * Panel: **Payment List**
2. Enter one or more search criteria describing the desired EOB/ payment
    * Criteria are same as in **"Workflow: Log EOB"** above.
3. Click **'Search**' button to generate list of EOB's
4. Note Columns, **"Pay Status"** and **"Undistributed"** to see which are available
5. Click on any blue text in the EOB line
6. **"Edit Payment"** popup appears with identical functionality beginning at step \#4 in **"Workflow: Log and Allocate EOB"**, found above.

Reports
-------
Two types of reports: Clinic-wide and Patient. Select report parameters then generate report

### Clinic-wide
* Report generation is similar in all reports:
    1.  Set report date range
    2.  Set other parameters e.g., participants names, code numbers, amount of detail of report
    3.  Click **Submit** button
    4.  Click button for optional export or printing.

*(for complete listing of all clinic-wide reports see [OpenEMR wiki](http://open-emr.org/wiki/index.php/Main_Screen_%26_Navigation_4.1.3#Reports))*

### Patient Reports
* Multifaceted report on one selected Patient.
    1.  In Patient's Summary Screen, click on "Report" link at top under Patient name.
    2.  Select desired reports
    3.  Select output option

***Reports available:***
* Continuity of Care Record (CCR)
* Set optional date range
* Click 'View/ Print' button to view in web browser
* Click 'Download' button for .xml format zip archive
* Continuity of Care Document (CCD)
* Click 'View/ Print' button to view in web browser
* Click 'Download' button for .xml format zip archive
* Patient Report
* Select desired items in all three areas "Patient Report", "Issues" and "Documents"
* Click 'Generate Report' button to view in OpenEMR panel ''may be searched or printed out by links in panel's top section ''
* Click 'Download PDF' button to save pdf to hard drive

Customizing OpenEMR
===================

Build Custom Forms
----------------------------

The Layout Based Visit Form (LBV Form) is the basic type of custom data collection form, to which may be added the functionality
found in the NationNotes forms.

***See the User Manual Supplementary Topics, [Sample Layout Based Visit Form Tutorial](http://open-emr.org/wiki/index.php/Sample_Layout_Based_Visit_Form)
and [Sample NationNotes Form](http://open-emr.org/wiki/index.php/Sample_NationNotes_Form)***

Import Others' Custom OpenEMR Forms
----------------------------------------------------------

Many facilities that adopt OpenEMR create their own custom forms then contribute them back to the OpenEMR Project. These
forms are available in every OpenEMR installation; instructions for incorporating them into your clinic's setup are found.

***See the User Manual Supplementary Topic, [Contributed Forms](http://open-emr.org/wiki/index.php/OpenEMR_Contributed_Forms)***

<!--
[Category:User Guide 4.2.0](Category:User Guide 4.2.0 "wikilink")
[Category:User Guide](Category:User Guide "wikilink") -->
