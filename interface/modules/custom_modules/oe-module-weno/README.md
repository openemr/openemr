# Module Install and Setup.
- To start, go to OpenEMRs top menu and select **Modules->Manage Modules**  whereby the Module Manager (MM) page is presented. For brevity the below screenshot shows when the Weno module is installed and the Config Settings cog icon is clicked. Experienced users will note that I redesigned the MM adding new features outlined on another topic on this forum and refactoring the old two tab page to a single page.
- The **Install** button will become an **Enable** button after install completes. **Click Confi cog/gear** icon or **Enable**.
- If this is the first time the module has been installed a warning will pop up telling that Weno Admin settings have not been completed and validated. This is indicated by a red module config icon. By clicking this icon whether red or normal the **Weno eRx Service Admin Setup** settings will show in a panel. The module can not be enabled until the Primary Admin section passes validation.
- The trash can icon is used to unregister the module. Any previous Weno setup will persist and remain in the last state of configuration. This way when and if the module is re-registered, all previous setup will remain.

- **Important to note that The Primary Admin Section** will require using the Validate and Save button after completing this section. All other sections will auto save when values are changed.
## Setup Summary
- There are three sections. After entering the required Admin credentials, Weno User ID for all prescribers and the Weno Location ID for the appropriate facility, all of which was received when a Weno account was created, click the **Enable** button to enable the module allowing the start of initial pharmacies download. You may then go to the User Settings page to enter the prescribers credentials. For yourself in this case.
  All providers that will be prescribing using Weno eRx must also have their credentials set otherwise the Weno eRx widget will not display.
- After a log out and in or by clicking the **Restart OpenEMR** button in config panel the Weno menu items of **Admin->Other->Weno Management** and **Reports->Clients->Prescription Log** will be enabled.
## Weno Required and Ancillary Setup for OpenEMR
- Weno provided values necessary for setup are:
    1. The account holders Admin credentials.
    2. Weno User Id: Uxxxx
    3. Assigned Location Id's for all the facilities used by the above User Id: Lxxxxx
    4. The users credentials assign to each prescriber: username(email address) and password.
-  **Important!** It is good practice for all user/prescribers to have their default facility set in their Users settings. Otherwise the location from the first/default from all practice facilities will be used.

There are three sections within the Weno eRx Service Admin Setup that allow the user to setup almost all the necessary settings to successfully start e-prescribing. The only other item is that each Weno prescriber credentials are set up in their User Settings.
### The Weno Primary Admin Section.
- All values must be entered and validated.
- If validation fails because either email and/or password are invalid an alert will be shown stating such.
- If the encryption key is deemed invalid an alert will show and a new Encryption Reset button enabled. First try re-entering the key but if that doesn't work clicking the Reset button will create a new key. This change will also be reflected in the Admins main Weno account and no other actions are needed by the user. You may look on the key as an API token which may be a more familiar term to the reader.
### The Map Weno User Id`s (Required)  Section.
- This section presents a table of all authorised users showing their default facility if assigned and an input field to enter their Weno user id Uxxxx. This value is important in order to form a relationship between Weno and the OpenEMR user for tracking prescriptions.
- All values are automatically saved for the user whenever the Weno User ID is entered or changed.
- As a convenience, an edit button is supplied to present a dialog containing the Users settings in edit mode. From here user may edit any setting such as assigning a default facility. This would be the same as accessing Users from top menu Admin->Users selected Weno Prescriber.
### The Map Weno Facility Id`s (Required)  Section.
- This section is pretty self explanatory with perhaps noting this same data may be accessed from top menu Admin->Other->Weno Management as explained below.
- This section also auto saves for convenience.
### Other methods for various set up items accessed from top menu.
- Open **Admin->Users** and select the user associated with the weno user id Uxxx and enter and save the weno user id in the **Weno User ID** field.
- Next  open **Admin->Other->Weno Management** and enter the assigned Location Id Lxxxxx for the locations facilities.
- Lastly from the top patient bar user icon click **Settings**. Scroll down or find the Weno button and click. Enter your username(email) and password in the **Weno User Email and Weno User Password** fields and **Save**. **Note** If these credentials are absent or wrong, you will not be able to prescribe prescriptions.
