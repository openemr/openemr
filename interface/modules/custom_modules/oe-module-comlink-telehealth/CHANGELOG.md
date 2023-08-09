V2.0.0
- Add participant (add a third party participant) as either a new patient or using existing patient search
- Redesigned layout with collapsible participant list
- Controls fade out after five seconds of mouse / touch screen inactivity
- Share screen with participants
- Patient invitation email when telehealth session starts
- Third party invitation email when third party recipient is added
- Direct patient launch screen from email (will redirect to login page if not logged in)
- Improved OpenEMR email validation for patient portal and patient db insert
- Telehealth hooks on email invitations for developer enhancements (such as sending invitation via SMS)
- Minimized note taking session screen default position setting (Top Left, Bottom Left, Top Right, Bottom Right options)
- Minimized session screen will remember drag position during session if you move it to another location
- Redesigned mobile view to better support both landscape and portrait displays
- Pinned participants allow you to select a participant you want to always show in the main presentation screen
- Pending appointments no longer show a launch icon for patient appointments
- Pending appointments launched by a provider will automatically convert into a regular appointment instead of requiring the status to be changed before they can launch
- Participants now show the name of the person on the video screen
  If the local camera / microphone cannot be acquired it notifies the user of the device failure
- Bug Fix - Reassigning launched telehealth session now correctly allows patient access
- Bug Fix - prevent patient from immediately rejoining closed session when provider leaves
- Bug Fix - improved load times by lazy loading singleton service classes
- Bug Fix - expired sessions would show the launch button on the add edit event screen which would then display an error when clicked
- Bug Fix - Launching a second telehealth session in the same day for the same patient would often grab the earlier session for the patient and the patient would be unable to join
- Bug Fix - added ACL checks on the room controller action dispatcher to prevent improper access to the api
  
v1.2.0
- Added registration code capability for mobile app use
- Add App registration and use instructions to patient credentials screen
- Moved some of the classes and code around into directory structure for better maintainability
- Added payment and registration support