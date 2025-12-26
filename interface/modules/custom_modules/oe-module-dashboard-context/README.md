# Dashboard Context Manager Module for OpenEMR

A comprehensive module for managing patient dashboard widget visibility based on care contexts such as Primary Care, Outpatient, Emergency, Specialty, and more.

## Features

- **Multiple Care Contexts**: Pre-configured contexts for Primary Care, Outpatient, Inpatient, Emergency, Specialty, Telehealth, Behavioral Health, Pediatric, and Geriatric care settings
- **User-Level Customization**: Users can switch contexts and customize which widgets appear on their dashboard
- **Admin Management**: Administrators can create custom contexts, assign contexts to users, and set role-based defaults
- **Context Locking**: Admins can lock users to specific contexts when needed
- **Bulk Operations**: Assign contexts to multiple users at once
- **Role Defaults**: Set default contexts for different user roles (Physician, Nurse, Front Office, etc.)
- **Facility Defaults**: Configure default contexts per facility
- **Audit Logging**: Track all context changes for compliance
- **Import/Export**: Share context configurations between installations

## Installation

1. Download or clone this module to:
   ```
   [openemr]/interface/modules/custom_modules/oe-module-dashboard-context/
   ```

2. Navigate to **Admin > System > Modules** in OpenEMR

3. Find "Dashboard Context Manager" and click **Install**

4. After installation, click **Enable** to activate the module

## Usage

### For Users

1. On the patient dashboard, you'll see a "Dashboard Context" widget
2. Select your desired context from the dropdown
3. Click the gear icon to customize which widgets appear for that context
4. Create custom contexts for your specific workflow needs

### For Administrators

1. Navigate to **Admin > Dashboard Contexts**
2. **Contexts Tab**: Create, edit, and delete custom contexts
3. **User Assignments Tab**: Assign contexts to specific users, with optional locking
4. **Role Defaults Tab**: Set default contexts for user roles
5. **Statistics Tab**: View usage statistics across contexts
6. **Audit Log Tab**: Review context change history

## Available Contexts

| Context | Description |
|---------|-------------|
| Primary Care | Full view with all standard primary care widgets |
| Outpatient | Optimized for outpatient clinic visits |
| Inpatient | Essential widgets for hospital admissions |
| Emergency | Minimal, critical-only widgets for ED |
| Specialty | Focused view for specialty consultations |
| Telehealth | Virtual visit optimized widgets |
| Behavioral Health | Mental health focused configuration |
| Pediatric | Child-specific widgets and immunizations |
| Geriatric | Elderly care with preferences and care planning |

## Widget Control

The following widgets can be controlled per context:

- Demographics
- Insurance
- Billing
- Care Team
- Care Experience Preferences
- Treatment Preferences
- Allergies
- Medical Problems
- Medications
- Prescriptions
- Immunizations
- Messages
- Patient Reminders
- Disclosures
- Amendments
- Lab Results
- Vitals
- Clinical Reminders
- Appointments
- Recalls
- Tracks
- Portal

## Global Settings

| Setting | Description | Default |
|---------|-------------|---------|
| `dashboard_context_enabled` | Enable/disable the module | Yes |
| `dashboard_context_user_can_switch` | Allow users to switch contexts | Yes |
| `dashboard_context_show_widget` | Show context selector on dashboard | Yes |

## Database Tables

- `user_dashboard_context` - User preferences
- `dashboard_context_definitions` - Custom context definitions
- `dashboard_widget_order` - Widget ordering
- `dashboard_context_assignments` - Admin assignments
- `dashboard_context_role_defaults` - Role-based defaults
- `dashboard_context_facility_defaults` - Facility defaults
- `dashboard_context_audit_log` - Audit trail

## API

The module provides AJAX endpoints for integration:

- `/public/ajax.php` - User operations
- `/public/admin_ajax.php` - Admin operations

## Requirements

- OpenEMR 7.0.0 or higher
- PHP 8.1 or higher

## License

GNU General Public License 3

## Author

Jerry Padgett <sjpadgett@gmail.com>

## Support

For issues and feature requests, please use the OpenEMR GitHub repository.
