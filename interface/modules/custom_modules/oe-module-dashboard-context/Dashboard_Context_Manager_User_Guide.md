# Dashboard Context Manager - User Guide

## Introduction

The **Dashboard Context Manager** is a new OpenEMR module that allows healthcare staff to customize their patient dashboard view based on their current care context. Instead of seeing all 25+ dashboard widgets at once, users can switch between focused views optimized for different clinical scenarios.

Whether you're seeing a patient in the Emergency Department, conducting a telehealth visit, or providing routine primary care, the Dashboard Context Manager ensures you see only the most relevant information for your current workflow.

---

## Quick Start

### Switching Contexts

1. Open any patient's dashboard
2. Look for the **"Select and Change Care Context"** widget (typically in the right column)
3. Select your desired context from the dropdown:
   - Primary Care
   - Outpatient
   - Inpatient
   - Emergency
   - Specialty
   - Telehealth
   - Behavioral Health
   - Pediatric
   - Geriatric
4. The dashboard immediately updates to show only relevant widgets

### Customizing Your View

1. Click the **gear icon** (⚙️) next to the context selector
2. In the settings dialog, check/uncheck widgets to show or hide them
3. Click **Save Settings** to apply your changes
4. Your customizations are saved per context - each context remembers its own settings

---

## Available Care Contexts

### Primary Care
**Use for:** Routine office visits, annual physicals, follow-ups

Shows all widgets including demographics, problems, medications, allergies, vitals, lab results, immunizations, care team, reminders, and more. This is the most comprehensive view.

### Outpatient
**Use for:** Clinic visits, minor procedures, consultations

Balanced view showing demographics, insurance, problems, medications, allergies, vitals, labs, appointments, clinical reminders, and immunizations.

### Inpatient
**Use for:** Hospital admissions, inpatient care

Focused on active care: demographics, insurance, problems, medications, allergies, vitals, labs, notes, care team, and advance directives.

### Emergency
**Use for:** Emergency Department, urgent care, triage

Minimal critical information only: demographics, insurance, allergies, problems, medications, vitals, and advance directives. Designed for rapid assessment.

### Specialty
**Use for:** Specialist consultations, referrals

Shows demographics, insurance, billing, problems, medications, allergies, prescriptions, labs, notes, appointments, and care team.

### Telehealth
**Use for:** Virtual visits, remote consultations

Optimized for video visits: demographics, problems, medications, allergies, vitals, appointments, portal access, and patient photos.

### Behavioral Health
**Use for:** Mental health visits, therapy sessions, psychiatric care

Includes problems, medications, prescriptions, demographics, insurance, appointments, notes, care team, treatment preferences, care experience preferences, and health concerns.

### Pediatric
**Use for:** Well-child visits, pediatric care

Child-focused view with demographics, insurance, problems, medications, allergies, vitals, appointments, immunizations, reminders, care team, and portal access.

### Geriatric
**Use for:** Elderly patient care, geriatric assessments

Comprehensive elder care view including demographics, insurance, billing, problems, medications, prescriptions, allergies, vitals, appointments, recalls, care team, treatment preferences, care experience preferences, advance directives, and health concerns.

---

## Customizing Widget Visibility

Each context can be customized to show exactly the widgets you need:

### To Customize a Context:

1. Select the context you want to customize from the dropdown
2. Click the **gear icon** (⚙️)
3. In the **Widget Settings** dialog:
   - **Left panel**: Check/uncheck widgets to show or hide
   - **Right panel**: Create a new custom context (optional)
4. Click **Save Settings**

### Available Widgets:

| Widget | Description |
|--------|-------------|
| Demographics | Patient demographic information |
| Insurance | Insurance coverage details |
| Billing | Billing information |
| Care Team | Assigned care team members |
| Treatment Intervention Preferences | Patient treatment preferences |
| Care Experience Preferences | Patient care experience preferences |
| Allergies | Known allergies and reactions |
| Medical Problems | Problem list / diagnoses |
| Medications | Current medications |
| Prescriptions | Prescription history |
| Immunizations | Vaccination records |
| Patient Notes/Messages | Patient communications |
| Patient Reminders | Due reminders and alerts |
| Clinical Reminders | Clinical decision support reminders |
| Disclosures | Information disclosures |
| Amendments | Record amendments |
| Lab Results | Laboratory data |
| Vitals | Vital signs history |
| Appointments | Scheduled appointments |
| Recalls | Patient recall notices |
| Tracks | Track Anything data |
| Patient Portal / API Access | Portal status and API access |
| ID Card / Photos | Patient photos |
| Advance Directives | Advance directive documents |
| Health Concerns | Documented health concerns |

### Reset to Defaults

If you want to restore a context to its original configuration:
1. Open the Widget Settings dialog
2. Click **Reset to Defaults**
3. Confirm the reset

---

## Widget Ordering

Widgets on the patient dashboard can be reordered per context, so different care workflows can prioritize different information at the top of the screen.

### How Widget Ordering Works

- **Context-level order**: An administrator sets the default widget display order for each context
- **User-level override**: Individual users can save their own preferred order for any context
- **Precedence**: User override > Context default > System default (alphabetical by widget ID)

When a user switches contexts, the dashboard widgets are rearranged to match the configured order for that context. Widgets not included in the order list remain in their original position at the end.

### For Users

Widget ordering is configured through the admin interface. As a regular user, you benefit from the ordering set by your administrator. If user-level overrides are enabled, your personal order preference is saved automatically when you reorder widgets.

### For Administrators

See [Configuring Widget Order (Admin)](#configuring-widget-order-admin) below.

---

## Widget Label Renaming

Widget labels (the heading text shown on each dashboard card and in the settings dialog) can be customized per context. This allows organizations to use terminology that matches their clinical workflows.

### How Label Renaming Works

- Labels are set per context, not per user
- A custom label replaces the default label everywhere: in the widget card header and in the settings dialog checkbox list
- Removing a custom label restores the default label
- Labels are included in context export/import

### Examples

| Default Label | Custom Label (Example) | Context |
|---------------|----------------------|---------|
| Allergies | Known Allergies & Reactions | Emergency |
| Medical Problems | Active Problem List | Primary Care |
| Vitals | Current Vital Signs | Inpatient |
| Patient Notes/Messages | Clinical Notes | Behavioral Health |

### For Administrators

See [Configuring Widget Labels (Admin)](#configuring-widget-labels-admin) below.

---

## Creating Custom Contexts

Need a context for a specific workflow? Create your own!

### To Create a Custom Context:

1. Click the gear icon to open Widget Settings
2. In the right panel, enter:
   - **Context Name**: A descriptive name (e.g., "Pre-Op Assessment")
   - **Description**: Optional description of when to use this context
3. Configure the widget checkboxes for your custom context
4. Click **Create Context**
5. Your new context appears in the dropdown after the standard contexts

### Custom Context Tips:

- Start by customizing an existing context that's closest to your needs
- Use clear, descriptive names so colleagues understand the purpose
- Custom contexts are personal to your user account unless an admin makes them global

---

## Understanding Context Behavior

### How Contexts Work:

- **Per-User Settings**: Your context selection and customizations are saved to your user account
- **Per-Context Customization**: Each context maintains its own widget configuration
- **Instant Updates**: Switching contexts immediately shows/hides widgets without page reload
- **Persistent**: Your last selected context is remembered when you return to the dashboard

### What Gets Saved:

| Setting | Scope |
|---------|-------|
| Active context selection | Per user |
| Widget visibility settings | Per user, per context |
| Widget display order | Per context (admin default), per user (personal override) |
| Custom widget labels | Per context (admin-configured) |
| Custom context definitions | Per user (unless admin makes global) |

---

## Administrator Features

*Note: These features require administrative privileges*

### Admin Menu Location

**Admin → System → Dashboard Contexts**

### Admin Capabilities:

#### Context Management
- Create global custom contexts available to all users
- Edit or delete existing custom contexts
- Set widget defaults for each context

#### User Assignments
- Assign specific contexts to users
- Lock users to a context (prevents them from switching)
- Bulk assign contexts to multiple users

#### Role Defaults
- Set default contexts by user role:
  - Physician → Primary Care
  - Nurse → Primary Care
  - Front Office → Outpatient
  - Billing → Outpatient
  - Specialist → Specialty
  - Therapist → Behavioral Health
  - And more...

#### Facility Defaults
- Set default contexts per facility location

#### Configuring Widget Order (Admin)

Administrators can set the default widget display order for each context using drag-and-drop in the context editor.

**To set widget order:**

1. Go to **Admin > System > Dashboard Contexts**
2. Click **Edit** on an existing context, or click **Create New Context**
3. In the context dialog, each widget row has a **drag handle** (grip icon) on the left
4. **Drag widgets up or down** to set the desired display order
5. Click **Save** -- the widget order is saved alongside the widget visibility configuration

**Notes:**

- The order applies to all users assigned to that context (unless they have a personal override)
- Widgets hidden in the context (unchecked) are still included in the order list -- they simply won't appear on the dashboard
- If no order is configured, widgets appear in their default system order
- Widget order is included when exporting/importing context configurations

#### Configuring Widget Labels (Admin)

Administrators can rename widget labels per context to match organizational terminology.

**To rename a widget label:**

1. Go to **Admin > System > Dashboard Contexts**
2. Click **Edit** on a context
3. In the context dialog, each widget row has a **Custom Label** text field on the right
4. Type the desired label in the text field (leave blank to keep the default label)
5. Click **Save**

**To remove a custom label:**

1. Edit the context
2. Clear the custom label text field for the widget
3. Click **Save** -- the default label is restored

**Where custom labels appear:**

- Widget card headers on the patient dashboard
- Widget toggle checkboxes in the user settings dialog
- The admin context editor (next to the default label)

**Notes:**

- Custom labels are per-context, not per-user -- all users on the same context see the same labels
- Labels are included when exporting/importing context configurations
- Labels do not affect any underlying data or functionality, only the display text

#### Statistics & Audit
- View context usage statistics
- Review audit log of context changes
- Export/import context configurations (now includes widget order and labels)

---

## Troubleshooting

### Widgets Not Hiding/Showing

**Issue**: Some widgets don't respond to context changes

**Solutions**:
1. Refresh the browser page
2. Clear browser cache
3. Verify the widget ID is correctly configured (admin task)

### Context Not Saving

**Issue**: Context resets when navigating away

**Solutions**:
1. Check if your context is "locked" by an administrator
2. Ensure you're logged in with a valid session
3. Contact your system administrator

### "Context Locked" Message

**Issue**: Cannot switch contexts, see "locked by administrator" message

**Explanation**: Your administrator has assigned you a specific context and prevented switching. Contact your admin if you need a different context.

### Missing Widgets in Settings

**Issue**: Expected widget doesn't appear in the settings dialog

**Explanation**: Only widgets present on the dashboard can be controlled. If a widget is globally disabled in OpenEMR settings, it won't appear in the context manager.

---

## Best Practices

### For Clinical Staff:

1. **Choose the right context** before reviewing a patient - it helps focus your attention
2. **Customize sparingly** - the defaults are designed for typical workflows
3. **Create custom contexts** for recurring specialized workflows
4. **Use Emergency context** for urgent situations - it shows only critical information

### For Administrators:

1. **Set role defaults** to give users appropriate starting contexts
2. **Use locking judiciously** - only when context consistency is required
3. **Create global custom contexts** for organization-specific workflows
4. **Review audit logs** periodically for unusual context changes
5. **Train users** on context switching during onboarding

---

## Keyboard Shortcuts

Currently, context switching is done via the dropdown selector. Future versions may include keyboard shortcuts for rapid context switching.

---

## FAQ

**Q: Will switching contexts affect other users?**
A: No. Your context selection and customizations are personal to your user account.

**Q: Can I have different contexts for different patients?**
A: Currently, context is per-user, not per-patient. However, you can quickly switch contexts when moving between patients with different care needs.

**Q: Do context changes affect the medical record?**
A: No. Context only affects what you *see* on the dashboard. It does not modify any patient data.

**Q: Can I share my custom context with colleagues?**
A: Ask your administrator to make your custom context "global" so others can use it.

**Q: What happens if a widget I've hidden contains important alerts?**
A: Critical alerts and clinical decision support reminders should be configured to display regardless of context. Consult your administrator about alert visibility settings.

---

## Version Information

- **Module Version**: 1.1.0
- **Minimum OpenEMR Version**: 7.0.0
- **PHP Requirement**: 8.1+

---

## Support

For questions, issues, or feature requests:

- **OpenEMR Forums**: [community.open-emr.org](https://community.open-emr.org)
- **GitHub Issues**: [github.com/openemr/openemr](https://github.com/openemr/openemr)

---

## Credits

Developed by Jerry Padgett (sjpadgett@gmail.com) for the OpenEMR community.

Licensed under GNU General Public License v3.
