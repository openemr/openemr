# TeletonicMD Design System for OpenEMR

A comprehensive, accessible, and HIPAA-compliant design system for medical professionals using OpenEMR.

## 🏥 Overview

The TeletonicMD Design System provides a professional, modern, and accessible user interface theme specifically designed for healthcare applications. Built with medical workflows in mind, this design system ensures optimal usability for healthcare professionals while maintaining strict accessibility and compliance standards.

### Key Features

- ✅ **WCAG AA Compliant** - All colors and interactions meet accessibility standards
- ✅ **HIPAA Appropriate** - Professional medical interface design
- ✅ **Medical-First Design** - Optimized for clinical workflows
- ✅ **Responsive Design** - Works on desktop, tablet, and mobile devices
- ✅ **High Performance** - Optimized for medical application requirements
- ✅ **Comprehensive Documentation** - Full implementation guide included

## 🎨 Design Principles

### 1. **Medical Professional Aesthetics**
- Clean, uncluttered interfaces that reduce cognitive load
- Professional color palette inspired by medical environments
- High contrast ratios for clinical setting visibility
- Consistent visual hierarchy for quick information scanning

### 2. **Accessibility First**
- WCAG 2.1 AA compliance across all components
- Screen reader optimized with proper ARIA implementation
- Keyboard navigation support for all interactions
- Motor accessibility with appropriate touch targets

### 3. **Clinical Workflow Optimization**
- Quick-scan information architecture
- Status indicators designed for medical contexts
- Alert systems with appropriate urgency levels
- Form design optimized for data entry efficiency

### 4. **Safety & Compliance**
- HIPAA-appropriate professional appearance
- High-visibility error and alert states
- Consistent interaction patterns to prevent user errors
- Clear information hierarchy for patient safety

## 📁 File Structure

```
interface/themes/teletonic_custom/
├── README.md                          # This documentation
├── ACCESSIBILITY_COMPLIANCE.md       # WCAG AA compliance report
├── main.scss                         # Main stylesheet with imports
├── base/
│   ├── _variables.scss               # Design tokens and color system
│   ├── _mixins.scss                  # Reusable style patterns
│   ├── _typography.scss              # Professional typography system
│   └── _components.scss              # UI component library
├── components/                       # Individual component files
├── layouts/                         # Page layout templates
└── utilities/                       # Utility classes and helpers
```

## 🎯 Color System

### Primary Medical Palette
Professional medical blues designed for clinical environments:

```scss
$primary-50: #e8f4f8;    // Lightest medical blue
$primary-500: #1e7a9a;   // Primary medical blue (brand)
$primary-700: #134650;   // Dark medical blue (text)
$primary-900: #081419;   // Deep medical blue (headings)
```

### Secondary Teal Palette
Complementary teal colors for accents and secondary actions:

```scss
$secondary-50: #e6f7f7;   // Lightest teal
$secondary-500: #1a9999;  // Primary teal
$secondary-700: #0f5454;  // Dark teal
```

### Semantic Colors
Medical-context semantic colors for alerts and status:

```scss
$success-600: #059669;    // Healthy/positive results
$warning-600: #d97706;    // Caution/attention required
$error-600: #dc2626;      // Critical/emergency
$info-600: #2563eb;       // General information
```

### Medical Alert Colors
High-visibility colors for medical alerts:

```scss
$alert-critical: #dc2626;   // Emergency/critical
$alert-high: #ea580c;       // High priority
$alert-medium: #ca8a04;     // Medium priority
$alert-resolved: #059669;   // Resolved/complete
```

## 📝 Typography

### Font Stack
Professional, readable fonts optimized for medical interfaces:

```scss
$font-family-primary: 'Inter', 'Segoe UI', system-ui, sans-serif;
$font-family-secondary: 'Source Sans Pro', 'Helvetica Neue', Arial, sans-serif;
$font-family-mono: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
```

### Type Scale
Modular scale (1.25 ratio) for consistent typography hierarchy:

```scss
$font-size-xs: 0.75rem;   // 12px - Small labels
$font-size-sm: 0.875rem;  // 14px - Secondary text
$font-size-base: 1rem;    // 16px - Body text
$font-size-lg: 1.125rem;  // 18px - Emphasized text
$font-size-xl: 1.25rem;   // 20px - Small headings
$font-size-2xl: 1.5rem;   // 24px - Section headings
$font-size-3xl: 1.875rem; // 30px - Page headings
```

## 🧩 Component Library

### Buttons
Professional medical interface buttons with multiple variants:

```html
<!-- Primary action button -->
<button class="btn btn-primary">Save Patient Data</button>

<!-- Secondary action button -->
<button class="btn btn-secondary">Cancel</button>

<!-- Success button for positive actions -->
<button class="btn btn-success">Approve Treatment</button>

<!-- Danger button for critical actions -->
<button class="btn btn-danger">Delete Record</button>

<!-- Loading state -->
<button class="btn btn-primary btn-loading">Processing...</button>
```

### Form Controls
Accessible form inputs optimized for medical data entry:

```html
<div class="form-group">
  <label class="form-label required">Patient Name</label>
  <input type="text" class="form-input" required>
  <div class="form-help">Enter patient's full legal name</div>
</div>

<div class="form-group medical-data-group">
  <label class="form-label">Blood Pressure</label>
  <input type="text" class="form-input" placeholder="120/80">
</div>
```

### Cards
Professional information display cards:

```html
<!-- Patient information card -->
<div class="card patient-card">
  <div class="card-header">
    <div class="patient-name">John Doe</div>
    <div class="patient-id">MRN: 12345678</div>
  </div>
  <div class="card-body">
    <p>Patient details and medical information...</p>
  </div>
</div>

<!-- Medical alert card -->
<div class="card alert-card">
  <div class="card-header">
    <h4 class="card-title">Medical Alert</h4>
  </div>
  <div class="card-body">
    <p>Important medical information requiring attention.</p>
  </div>
</div>
```

### Alerts & Notifications
Medical-appropriate alert system:

```html
<!-- Success alert -->
<div class="alert alert-success">
  <div class="alert-content">
    <div class="alert-title">Treatment Completed</div>
    <div class="alert-description">Patient treatment has been successfully recorded.</div>
  </div>
</div>

<!-- Emergency alert -->
<div class="alert alert-emergency">
  <div class="alert-content">
    <div class="alert-title">Emergency Alert</div>
    <div class="alert-description">Critical patient information requires immediate attention.</div>
  </div>
</div>
```

### Status Badges
Clinical status indicators:

```html
<!-- Active patient status -->
<span class="status-badge status-active">Active</span>

<!-- Pending review status -->
<span class="status-badge status-pending">Pending Review</span>

<!-- Critical status -->
<span class="status-badge status-critical">Critical</span>

<!-- Priority badges -->
<span class="status-badge priority-emergency">Emergency</span>
<span class="status-badge priority-urgent">Urgent</span>
<span class="status-badge priority-routine">Routine</span>
```

### Medical Data Tables
Professional medical data display:

```html
<div class="medical-table-responsive">
  <table class="medical-table patient-table">
    <thead>
      <tr>
        <th>Patient Name</th>
        <th>MRN</th>
        <th>Last Visit</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr class="patient-row">
        <td class="patient-name">Jane Smith</td>
        <td>87654321</td>
        <td>2025-09-20</td>
        <td><span class="status-badge status-active">Active</span></td>
      </tr>
    </tbody>
  </table>
</div>
```

## 🚀 Implementation Guide

### 1. Basic Setup

Add the main stylesheet to your OpenEMR template:

```html
<link rel="stylesheet" href="interface/themes/teletonic_custom/main.css">
```

### 2. Compiling SCSS

If using SCSS compilation:

```bash
# Install dependencies
npm install sass

# Compile main SCSS file
sass interface/themes/teletonic_custom/main.scss interface/themes/teletonic_custom/main.css

# Watch for changes during development
sass --watch interface/themes/teletonic_custom/main.scss:interface/themes/teletonic_custom/main.css
```

### 3. OpenEMR Integration

Update your OpenEMR theme configuration to use the TeletonicMD theme:

```php
// In globals.php or theme configuration
$GLOBALS['theme_tabs_layout'] = 'teletonic_custom';
```

### 4. Customization

#### Color Customization
Override design tokens by modifying `_variables.scss`:

```scss
// Custom primary color
$primary-500: #your-brand-color;

// Custom secondary color
$secondary-500: #your-accent-color;
```

#### Component Customization
Extend components using the provided mixins:

```scss
.custom-button {
  @include button-base;
  @include button-primary;
  // Add your custom styles
}
```

## ♿ Accessibility Features

### WCAG AA Compliance
- All color combinations meet 4.5:1 contrast ratio
- Focus indicators visible and high contrast
- Keyboard navigation support for all interactive elements
- Screen reader optimized with proper ARIA labels

### Medical-Specific Accessibility
- High contrast alert colors for emergency situations
- Clear visual hierarchy for quick information scanning
- Consistent interaction patterns to prevent medical errors
- Touch-friendly targets for mobile medical cart usage

### Testing Recommendations
1. Test with screen readers (NVDA, VoiceOver, JAWS)
2. Validate keyboard-only navigation
3. Check color contrast with automated tools
4. Test at 200% zoom level
5. Validate with medical professionals

## 📱 Responsive Design

### Breakpoints
Mobile-first responsive design with medical workflow considerations:

```scss
$breakpoint-sm: 640px;   // Small tablets
$breakpoint-md: 768px;   // Tablets
$breakpoint-lg: 1024px;  // Small desktops
$breakpoint-xl: 1280px;  // Large desktops
```

### Mobile Optimizations
- Touch-friendly 44px minimum touch targets
- Readable text at mobile sizes
- Optimized forms for mobile data entry
- Responsive tables with horizontal scroll

## 🎯 Medical Use Cases

### Patient Management Interface
- Clear patient identification headers
- Status indicators for patient conditions
- High-contrast medical alerts
- Efficient form layouts for data entry

### Clinical Documentation
- Readable typography for long-form content
- Professional color scheme for clinical settings
- Clear visual hierarchy for medical records
- Print-optimized styles for medical documents

### Emergency Interface
- High-visibility emergency alerts
- Quick-access button styling
- Clear status indicators
- Mobile-optimized for emergency situations

## 🔧 Customization Options

### Theme Variants
Create custom theme variants by overriding CSS custom properties:

```css
:root {
  --color-primary-500: #your-primary-color;
  --color-secondary-500: #your-secondary-color;
}
```

### Facility Branding
Add facility-specific branding while maintaining compliance:

```scss
.facility-branding {
  --custom-primary: #facility-color;
  --custom-accent: #facility-accent;
}
```

### Component Extensions
Extend existing components for specific medical workflows:

```scss
.medication-form {
  @extend .medical-form;

  .dosage-input {
    @include input-base;
    font-family: $font-family-mono;
  }
}
```

## 🏗️ Development Guidelines

### Code Standards
- Follow BEM methodology for class naming
- Use semantic HTML elements
- Maintain accessibility attributes
- Comment complex mixins and functions

### Performance Considerations
- Optimize for medical application load times
- Use efficient CSS selectors
- Minimize repaints for smooth interactions
- Implement critical CSS for above-fold content

### Testing Requirements
- Cross-browser testing (Chrome, Firefox, Safari, Edge)
- Screen reader testing
- Mobile device testing
- Print stylesheet validation

## 📚 Advanced Features

### High Contrast Mode Support
Automatic adaptation for users with visual impairments:

```scss
@media (prefers-contrast: high) {
  :root {
    --color-primary-500: #0066cc;
    --color-neutral-900: #000000;
  }
}
```

### Reduced Motion Support
Respects user preferences for reduced motion:

```scss
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

### Print Optimization
Medical document printing styles:

```scss
@media print {
  .medical-table th,
  .medical-table td {
    border: 1px solid #000;
  }

  .patient-banner {
    border: 2px solid #000;
  }
}
```

## 🤝 Contributing

### Design System Updates
1. Maintain accessibility standards
2. Test with medical professionals
3. Validate HIPAA compliance considerations
4. Update documentation with changes

### Bug Reports
Include the following information:
- Browser and version
- OpenEMR version
- Steps to reproduce
- Expected vs actual behavior
- Screenshots if applicable

## 📄 License

This design system is created specifically for OpenEMR and TeletonicMD integration. Licensed under the same terms as OpenEMR.

## 📞 Support

For implementation questions or customization requests:
- Review the documentation thoroughly
- Check accessibility compliance requirements
- Test with medical professionals before deployment
- Validate HIPAA compliance for any customizations

---

**Version**: 1.0.0
**Last Updated**: September 24, 2025
**Compatibility**: OpenEMR 7.0+
**Standards**: WCAG 2.1 AA, Section 508, HIPAA Visual Standards