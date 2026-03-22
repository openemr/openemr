# MedExBank AI API Specification

## Overview

This document defines the API contract between OpenEMR (client) and MedExBank SaaS (server) for AI-powered scheduling features.

**IMPORTANT:** All intelligence, algorithms, and models remain at MedExBank. OpenEMR only receives predictions/suggestions.

## Authentication

All requests require Bearer token authentication:

```
Authorization: Bearer {api_key}
```

API keys are stored in OpenEMR's `medex_prefs.ME_api_key` field.

## Base URL

Production: `https://api.medexbank.com`
Staging: `https://staging-api.medexbank.com`

---

## Endpoints

### 1. No-Show Prediction

**POST** `/api/v2/ai/predict-noshow`

Predicts probability of patient no-show based on historical patterns.

**Request Body:**
```json
{
  "event": {
    "pc_eid": 12345,
    "pc_eventDate": "2026-02-15",
    "pc_startTime": "09:00:00",
    "pc_duration": 900,
    "pc_catid": 5
  },
  "patient_history": [
    {
      "pc_eventDate": "2026-01-10",
      "pc_apptstatus": "@"
    },
    {
      "pc_eventDate": "2025-12-15",
      "pc_apptstatus": "x"
    }
  ]
}
```

**Response:**
```json
{
  "risk": 0.73,
  "confidence": 0.85,
  "factors": [
    {
      "factor": "previous_noshow",
      "impact": 0.45,
      "description": "Patient has 1 no-show in last 6 months"
    },
    {
      "factor": "appointment_time",
      "impact": 0.15,
      "description": "Early morning appointments show higher no-show rate"
    },
    {
      "factor": "advance_booking",
      "impact": 0.13,
      "description": "Appointment booked 5 weeks in advance"
    }
  ],
  "recommendation": "Send confirmation SMS 24 hours before appointment"
}
```

**Risk Levels:**
- `0.0 - 0.3`: Low risk (green)
- `0.3 - 0.7`: Medium risk (yellow)
- `0.7 - 1.0`: High risk (red)

---

### 2. Schedule Template Suggestions

**POST** `/api/v2/ai/suggest-templates`

Analyzes appointment history and suggests optimal schedule templates.

**Request Body:**
```json
{
  "provider_id": 5,
  "appointment_history": [
    {
      "pc_eventDate": "2026-01-15",
      "pc_startTime": "09:00:00",
      "pc_duration": 1800,
      "pc_catid": 10,
      "pc_pid": 123
    }
  ]
}
```

**Response:**
```json
{
  "suggestions": [
    {
      "template_name": "Monday Morning - New Patients",
      "day_of_week": 1,
      "start_time": "09:00:00",
      "end_time": "12:00:00",
      "preferred_category_id": 10,
      "slot_duration": 30,
      "confidence": 0.89,
      "reasoning": "85% of Monday mornings allocated to new patients. Average 30-min duration.",
      "expected_revenue": 2400.00,
      "utilization_rate": 0.92
    },
    {
      "template_name": "Afternoon Follow-ups",
      "day_of_week": 1,
      "start_time": "14:00:00",
      "end_time": "17:00:00",
      "preferred_category_id": 9,
      "slot_duration": 15,
      "confidence": 0.82,
      "reasoning": "Consistent pattern of 15-min follow-ups after lunch.",
      "expected_revenue": 1800.00,
      "utilization_rate": 0.88
    }
  ],
  "patterns_detected": {
    "consistency_score": 0.85,
    "weeks_analyzed": 12,
    "total_appointments": 245
  }
}
```

---

### 3. Reschedule Slot Suggestions

**POST** `/api/v2/ai/suggest-reschedule`

Finds optimal alternative time slots when rescheduling.

**Request Body:**
```json
{
  "original_event": {
    "pc_eid": 12345,
    "pc_eventDate": "2026-02-15",
    "pc_startTime": "09:00:00",
    "pc_aid": 5,
    "pc_pid": 123,
    "pc_duration": 900
  },
  "available_slots": [
    {
      "pc_eventDate": "2026-02-16",
      "pc_startTime": "10:00:00",
      "pc_endTime": "12:00:00",
      "pc_prefcatid": 5
    }
  ],
  "preferences": {
    "preferred_days": [1, 2, 3],
    "preferred_time": "morning",
    "max_wait_days": 14
  }
}
```

**Response:**
```json
{
  "suggested_slots": [
    {
      "date": "2026-02-16",
      "start_time": "10:00:00",
      "score": 0.95,
      "reasons": [
        "Matches preferred time (morning)",
        "Next available day",
        "Same provider availability",
        "High historical patient satisfaction for this slot"
      ],
      "conflicts": 0,
      "patient_convenience_score": 0.92
    },
    {
      "date": "2026-02-17",
      "start_time": "09:30:00",
      "score": 0.88,
      "reasons": [
        "Similar time to original",
        "2 days later"
      ],
      "conflicts": 0,
      "patient_convenience_score": 0.85
    }
  ]
}
```

---

### 4. Revenue Insights

**POST** `/api/v2/ai/revenue-insights`

Provides revenue optimization suggestions for provider's schedule.

**Request Body:**
```json
{
  "provider_id": 5,
  "date": "2026-02-15",
  "schedule": [
    {
      "pc_eid": 100,
      "pc_startTime": "09:00:00",
      "pc_duration": 1800,
      "pc_catname": "New Patient",
      "pc_pid": 123
    }
  ]
}
```

**Response:**
```json
{
  "date": "2026-02-15",
  "total_scheduled_revenue": 3200.00,
  "potential_revenue": 4500.00,
  "utilization_rate": 0.71,
  "insights": [
    {
      "type": "gap_opportunity",
      "time": "11:00:00",
      "duration": 60,
      "potential_revenue": 400.00,
      "suggestion": "Fill 1-hour gap with 4x 15-min follow-ups"
    },
    {
      "type": "slot_optimization",
      "time": "14:00:00",
      "current_category": "Follow-up",
      "suggested_category": "New Patient",
      "revenue_increase": 150.00,
      "reasoning": "Historical data shows Monday afternoons have high new patient demand"
    }
  ],
  "benchmark": {
    "your_revenue_per_hour": 400.00,
    "practice_average": 385.00,
    "top_quartile": 525.00
  }
}
```

---

## Error Responses

All endpoints return consistent error format:

```json
{
  "error": {
    "code": "INVALID_API_KEY",
    "message": "API key is invalid or expired",
    "details": null
  }
}
```

**Error Codes:**
- `INVALID_API_KEY` - Authentication failed
- `INSUFFICIENT_DATA` - Not enough historical data for prediction
- `RATE_LIMIT_EXCEEDED` - Too many requests
- `SERVICE_UNAVAILABLE` - MedExBank service down

---

## Rate Limits

- 1000 requests/hour per practice
- 10,000 requests/day per practice
- Burst: 100 requests/minute

## Data Privacy

- Patient PHI is transmitted over HTTPS
- Data retention: 30 days for caching
- No data sold or shared
- HIPAA compliant

## Webhook Notifications (Optional)

MedExBank can push updates to OpenEMR:

**POST** `{openemr_url}/interface/modules/custom_modules/oe-module-medex/public/api/webhooks/ai-update.php`

```json
{
  "event_type": "noshow_prediction_updated",
  "event_id": 12345,
  "new_risk": 0.85,
  "timestamp": "2026-02-15T10:00:00Z"
}
```

---

## Implementation Checklist for MedExBank

### Required Endpoints
- [ ] POST /api/v2/ai/predict-noshow
- [ ] POST /api/v2/ai/suggest-templates
- [ ] POST /api/v2/ai/suggest-reschedule
- [ ] POST /api/v2/ai/revenue-insights

### Optional Enhancements
- [ ] Webhook support for real-time updates
- [ ] Batch prediction endpoint
- [ ] A/B testing framework
- [ ] Model performance metrics API

### Infrastructure
- [ ] Authentication middleware
- [ ] Rate limiting
- [ ] Request logging
- [ ] Error tracking
- [ ] Load balancing

---

**Version:** 1.0
**Last Updated:** January 29, 2026
**Contact:** dev@medexbank.com
