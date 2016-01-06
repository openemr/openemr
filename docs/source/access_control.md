Access Controls
===============

Access controls are used throughout OpenEMR to control access by user
roles. It can be configured at `Administration -> ACL` . **This document is
still under construction.**

Administration (admin)
----------------------

### Superuser

Can delete patients, encounters, issues (super)

* Authorizations are:
    - Configuring Globals (ie. Settings)
    - Using the External Data Loads module
    - Using the Backup module
    - Administering Lists (including LBF Module)
    - Administering Layouts (including LBF Module)
    - Deleting Patients
    - Deleting Issues
    - Deleting Patient Notes
    - Deleting Encounters
    - Deleting Forms
    - Deleting Transactions
    - Deleting Bills
    - Deleting items in the Pharmacy dispensary module
    - Delete scanned forms (from contrib scanned forms module)
* Return Values: none

### Calendar Settings
calendar

* Allow administration of the appointment categories.
* Return Values: none

### Database Reporting
database

* Allows use of the embedded phpmyadmin module.
* Return Values: none

### Forms Administration
forms

* Allow administration of forms(adding/activiation/deactivating/categorizing).
* Return Values: none

### Practice Settings
practice

* Allows administration of practice settings (such as pharmacies, insurance companies, insurance numbers, X12 partners and outside facilities address book).
* Return Values: none

### Superbill Codes Administration
 (superbill)

* Allow administration of service codes.
* Return Values: none

### Users/Groups/Logs Administration
(users)

* Allows administration of user specific settings, such as user information, user logs, groups, user SSL certificates and facilities.
* Return Codes: none

### Batch Communication Tool (batchcom)

* Allows use of the batch communication tool.
* Return Values: none

### Language Interface Tool (language)

* Allows administration of the translations.
* Return Values: none

### Pharmacy Dispensary (drugs)

* Allows administration of the pharmacy dispensary.
* Return Values: none

### ACL Administration (acl)

* Allows administration of access controls.
* Return Values: none

Accounting (acct)
-------------------------

### Billing (write optional) (bill)

### Allowed to discount prices (in Fee Sheet or Checkout form) (disc)

### EOB Data Entry (eob)

### Financial Reporting - my encounters (rep)

### Financial Reporting - anything (rep\_a)

Patient Information (patients)
-------------------------------------------

### Appointments (write,wsome optional) (appt)

* Allows scheduling of appointments.
* Return Values:
    - wsome - Can schedule appointments (but can not double book or schedule appt outside of a providers calendar template).
    - write - Can schedule appointments (can double book and schedule appt outside of a providers calendar template)

### Demographics (write,addonly optional) (demo)

* Allows viewing and entering of patient demographics(and insurance).
* Return Values:
    - addonly - Allowed to enter new patient demographics.
    - write - Allowed to enter new patient demographics and modify current patient demographics.
    - ANY - Allowed to view patient demographics.

### Medical Records and History (write,addonly optional) (med)

* Allows viewing and entering in of medical records.
* Return Values:
    - addonly - Allowed to add medical records(specifically for adding new Issues).
    - write - Allowed to add and modify medical records(specifically for adding new or modifying current medical issues and entering in of the patient history).
    - ANY - Allowed to view medical records.

### Transactions, e.g. referrals (write optional) (trans)

* Not used yet.

### Documents (write,addonly optional) (docs)

* Not used yet.

### Patient Notes (write,addonly optional) (notes)

* Allows viewing and entering in of patient notes.

* Return Values:
    - addonly - Can enter in patient notes. (note there is no current difference between this and 'write')
    - write - Can enter in patient notes. (note there is no current difference between this and 'addonly')
    - ANY - Can view patient notes.

### Sign Lab Results (write,addonly optional) (sign)

* Allows signing of labs.
* Return Values: none

Encounter Information (encounters)
----------------------------------

### Authorize - my encounters (auth)

* Not used yet.

### Authorize - any encounters (auth\_a)

* Not used yet.

### Coding - my encounters (write,wsome optional) (coding)

### Coding - any encounters (write,wsome optional) (coding\_a)

### Notes - my encounters (write,addonly optional) (notes)

### Notes - any encounters (write,addonly optional) (notes\_a)

### Fix encounter dates - any encounters (date\_a)

### Less-private information (write,addonly optional) (relaxed)

Squads (squads)
---------------

*  Section "squads" applies to [Athletic Team](Administration_Globals#Specific_Application "wikilink") use only:
    - Access Controls in this section define the user-specified list of squads.

Sensitivities (sensitivities)
-----------------------------

* This section is to provide access control to more sensitive encounters(ie. a user would need the High access control to see encounters that are of High sensitivity).
* Return Values: none

### Normal (normal)

* User is able to see Normal sensitivity encounters.
* Return Values: none

### High (high)

* User is able to see High sensitivity encounters.
* Return Values: none

Lists (lists)
-------------

* This section is specific to the "List box w/add" items in the layouts/LBFs. If have access to this, then user can add items to these lists from the form.
* Return Values: none

### Default List (write,addonly optional) (default)

* This is specific to the "List box w/add" items in the layouts/LBFs. If have access to this, then user can add items to any of these lists on the forms that do not have a specific control for them.
* Return Values: none

### State List (write,addonly optional) (state)

* This is specific to the "List box w/add" items in the layouts/LBFs. If have access to this, then user can add items to the State list from the form.
* Return Values: none

### Country List (write,addonly optional) (country)

* This is specific to the "List box w/add" items in the layouts/LBFs. If have access to this, then user can add items to the Country list from the form.
* Return Values: none

### Language List (write,addonly optional) (language)

* This is specific to the "List box w/add" items in the layouts/LBFs. If have access to this, then user can add items to the Language list from the form.
* Return Values: none

### Ethnicity-Race List (write,addonly optional) (ethrace)

* This is specific to the "List box w/add" items in the layouts/LBFs. If have access to this, then user can add items to the Ethnicity-Race list from the form.
* Return Values: none

Placeholder (placeholder)
-------------------------

### Placeholder (Maintains empty ACLs) (filler)

* **Users**: Do not need to know what this is (if curious, read below *developer talk*)
* **Developers**: Simply used to ensure an ACL is never empty (note an ACL is filled with Access Controls); this is needed, because if all Access Controls are removed from an ACL, then the ACL will also be removed.
* Return Values: none

Nation Notes (nationnotes)
--------------------------

### Nation Notes (nn\_configure)

* This will allow configuration(contexts,templates, etc.) of Nation Notes module.
* Return Values: none

Patient Portal (patientportal)
------------------------------------------

### Patient Portal (portal)

* This will provide access to a third party portal.
* A 'Portal Activity' link at the top of the left menu will link to the third party portal at the address set in the [Offsite Patient Portal Site Address setting](Administration_Globals#Offsite_Patient_Portal_Site_Address "wikilink").
* Return Values: none
