# FaxSMS Module Setup Guide

## Overview

The FaxSMS module provides integrated communication services for OpenEMR including Fax, SMS, Email, and Voice capabilities. The module supports multiple vendors and allows granular user permissions for each service.

## Module Architecture

### Supported Services
- **Fax**: Send and receive fax documents
- **SMS**: Send text messages to patients
- **Email**: Send emails and reminders
- **Voice**: Voice widget functionality

### Supported Vendors
- **SMS Services**:
  - RingCentral SMS (ID: 1)
  - Twilio SMS (ID: 2)
  - Clickatell SMS (ID: 5)
- **Fax Services**:
  - RingCentral Fax (ID: 1)
  - etherFAX (ID: 3)
- **Email Services**:
  - Built-in Email Client (ID: 4)
- **Voice Services**:
  - RC Voice Widgets (ID: 6)

## Setup Process

### Phase 1: Enable Accounts (Global Configuration)

Administrators should navigate to **Services → Setup Services** in the OpenEMR menu.

#### Service Configuration
1. **Enable SMS Module**: Administrators can choose from disabled, RingCentral SMS, Twilio SMS, or Clickatell
2. **Enable Fax Module**: Options include disabled, RingCentral Fax, or etherFAX
3. **Enable Mail Client**: This setting enables or disables the email functionality
4. **Enable Voice Widgets**: This controls voice widget support

#### Additional Options
- **Enable Send SMS Dialog**: This option allows SMS sending from various UI locations
- **Individual User Accounts**: When enabled, this setting restricts users to their own account credentials with usage tracking by username

> **Note**: The form auto-saves when changes are made to any dropdown.

### Phase 2: Vendor Account Setup

After enabling services, administrators need to configure the actual vendor accounts:

#### Setup Options
- **Setup SMS**: Configure SMS vendor credentials
- **Setup Fax**: Configure fax vendor credentials
- **Setup Email**: Configure email server settings
- **Setup Voice**: Configure voice service settings

#### Interface Modes
- **Panel Mode**: Setup forms load in embedded panels
- **Dialog Mode**: Setup forms open in modal dialogs (administrators can toggle the "Use Dialog" checkbox)

### Phase 3: User Permissions

Administrators should click **User Permissions** to configure individual user access.

#### Permission Types
For each active user, administrators can set:
- **Fax Permission**: Allow the user to send/receive faxes
- **SMS Permission**: Allow the user to send SMS messages
- **Email Permission**: Allow the user to send emails
- **Voice Permission**: Allow the user to use voice widgets

#### Special Settings
- **Use Primary**: Users can utilize the primary account credentials instead of their own
- **Primary User**: Administrators can designate one user as the primary account holder for all services

#### Bulk Operations
- **Toggle All**: Check/uncheck all permissions for a specific service
- **Toggle User All Services**: Enable/disable all services for a specific user
- **Toggle All Use Primary**: Enable/disable primary account usage for all users

## Background Services Management

### Available Services
Background services can be configured for:
- **SMS**: Automated SMS notifications
- **Email**: Automated email reminders and notifications

### Service Actions
- **Create and Run**: Creates the background task but leaves it disabled
- **Enable**: Creates (if needed) and enables the background service
- **Disable**: Temporarily stops the background service
- **Delete**: Permanently removes the background service

### Service Configuration
- **Execute Interval**: Administrators can set how often the service runs (in hours, default: 24)
- **Status Display**: Shows current service status, run interval, and next execution time

### Important Notes
- When a service is first enabled, initial notifications run within 2 minutes
- Services must be enabled in Phase 1 before background services can be configured
- Each service can have its own execution schedule

## Permission Hierarchy

### Global Level
- The module must be enabled in the "Enable Accounts" section
- Vendor credentials must be configured

### User Level
- Individual users need specific service permissions
- Users can be restricted to their own credentials or use the primary account
- Only one user can be designated as the primary user

### Runtime Verification
The bootstrap process verifies permissions in this order:
1. Check if the global service is enabled
2. Check if the user has permission for the service
3. Apply appropriate credentials (user's own or primary account)

## Menu Integration

When services are enabled, the module automatically adds menu items:
- **SMS**: Appears as vendor-specific label (e.g., "RingCentral SMS")
- **Fax**: Appears as vendor-specific label (e.g., "RingCentral Fax")
- **Email**: Appears as "Clinic Email"
- **Notifications**: Sub-menu for email reminders (test and send)

## Security Features

### CSRF Protection
All forms include CSRF tokens for security against cross-site request forgery attacks.

### Access Control
- Menu items include ACL requirements
- Service permissions are enforced at both global and user levels
- Background services require admin privileges to configure

### Data Protection
- User credentials can be isolated when "Individual User Accounts" is enabled
- Usage tracking is tied to specific usernames
- Primary account access can be restricted per user

## Troubleshooting

### Common Issues
1. **Service not appearing in menu**: Check if the service is enabled in "Enable Accounts"
2. **User cannot access service**: Verify the user has appropriate permissions
3. **Background service not running**: Check service status and ensure it's enabled
4. **Credentials not working**: Verify vendor account setup is complete

### Setup Verification
1. Enable the service in Phase 1
2. Configure vendor credentials in Phase 2
3. Set user permissions in Phase 3
4. Test functionality through the service menu

## Best Practices

### Initial Setup
1. Enable only the services that are needed
2. Configure vendor credentials before setting user permissions
3. Test with a single user before rolling out to all users
4. Set up background services during low-usage periods

### User Management
1. Use the primary account feature for simplified credential management
2. Regularly review user permissions
3. Consider individual accounts for usage tracking and accountability
4. Train users on proper service usage

### Background Services
1. Set appropriate execution intervals based on organizational needs
2. Monitor service logs for any issues
3. Test background services before enabling in production
4. Have a backup plan if services fail

## Support and Maintenance

### Regular Tasks
- Review user permissions quarterly
- Monitor background service execution
- Update vendor credentials as needed
- Test services after OpenEMR updates

### Monitoring
- Check service status regularly
- Review usage logs if individual accounts are enabled
- Monitor vendor account limits and costs
- Verify background services are running as scheduled
