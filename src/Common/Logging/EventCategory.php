<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging;

enum EventCategory: string
{
    case PatientRecord = 'patient-record';
    case Scheduling = 'scheduling';
    case Order = 'order';
    case LabOrder = 'lab-order';
    case LabResult = 'lab-results';
    case SecurityAdministration = 'security-administration';
    case Other = 'other';
}
