# Vietnamese PT Module - Technical Architecture

**Version:** 1.0
**Last Updated:** 2025-11-22
**Author:** Dang Tran <tqvdang@msn.com>

## Table of Contents

- [Overview](#overview)
- [System Architecture](#system-architecture)
- [Component Diagrams](#component-diagrams)
- [Request Flow](#request-flow)
- [Service Layer Architecture](#service-layer-architecture)
- [Event System](#event-system)
- [Database Architecture](#database-architecture)
- [Integration Points](#integration-points)
- [Security Architecture](#security-architecture)

---

## Overview

The Vietnamese PT Module extends OpenEMR with comprehensive bilingual physiotherapy capabilities. It follows OpenEMR's modern architecture patterns with PSR-4 namespaced PHP, REST APIs, event-driven design, and service-oriented architecture.

### Core Design Principles

1. **Bilingual First**: Every data field has English and Vietnamese variants
2. **Service-Oriented**: Business logic isolated in service classes
3. **Event-Driven**: Services dispatch events for extensibility
4. **Validation-Centric**: All input validated before processing
5. **REST-Compliant**: Standard HTTP methods and status codes

---

## System Architecture

### High-Level Architecture

```mermaid
graph TB
    subgraph "Client Layer"
        UI[Web UI<br/>Forms & Widgets]
        API_CLIENT[API Clients<br/>External Systems]
    end

    subgraph "API Layer"
        REST[REST API Router<br/>apis/dispatch.php]
        ROUTES[Route Definitions<br/>_rest_routes_standard.inc.php]
    end

    subgraph "Controller Layer"
        ASSESS_CTL[PTAssessmentRestController]
        EXERCISE_CTL[PTExercisePrescriptionRestController]
        PLAN_CTL[PTTreatmentPlanRestController]
        OUTCOME_CTL[PTOutcomeMeasuresRestController]
        TERMS_CTL[VietnameseMedicalTermsRestController]
        TRANS_CTL[VietnameseTranslationRestController]
        INS_CTL[VietnameseInsuranceRestController]
        TEMPLATE_CTL[PTAssessmentTemplateRestController]
    end

    subgraph "Service Layer"
        ASSESS_SVC[PTAssessmentService]
        EXERCISE_SVC[PTExercisePrescriptionService]
        PLAN_SVC[PTTreatmentPlanService]
        OUTCOME_SVC[PTOutcomeMeasuresService]
        TERMS_SVC[VietnameseMedicalTermsService]
        TRANS_SVC[VietnameseTranslationService]
        INS_SVC[VietnameseInsuranceService]
        TEMPLATE_SVC[PTAssessmentTemplateService]
    end

    subgraph "Validation Layer"
        ASSESS_VAL[PTAssessmentValidator]
        EXERCISE_VAL[PTExercisePrescriptionValidator]
        PLAN_VAL[PTTreatmentPlanValidator]
        OUTCOME_VAL[PTOutcomeMeasuresValidator]
    end

    subgraph "Data Layer"
        DB[(MariaDB<br/>utf8mb4_vietnamese_ci)]
        STORED_PROC[Stored Procedures<br/>GetPatientAssessmentBilingual<br/>GetActiveExercisesBilingual]
        VIEWS[Database Views<br/>pt_patient_summary_bilingual]
        FUNCTIONS[Stored Functions<br/>get_vietnamese_term<br/>get_english_term]
    end

    subgraph "Event System"
        DISPATCHER[Symfony EventDispatcher]
        EVENTS[Domain Events<br/>BeforeAssessmentCreated<br/>AssessmentCreated<br/>etc.]
    end

    UI --> REST
    API_CLIENT --> REST
    REST --> ROUTES
    ROUTES --> ASSESS_CTL
    ROUTES --> EXERCISE_CTL
    ROUTES --> PLAN_CTL
    ROUTES --> OUTCOME_CTL
    ROUTES --> TERMS_CTL
    ROUTES --> TRANS_CTL
    ROUTES --> INS_CTL
    ROUTES --> TEMPLATE_CTL

    ASSESS_CTL --> ASSESS_SVC
    EXERCISE_CTL --> EXERCISE_SVC
    PLAN_CTL --> PLAN_SVC
    OUTCOME_CTL --> OUTCOME_SVC
    TERMS_CTL --> TERMS_SVC
    TRANS_CTL --> TRANS_SVC
    INS_CTL --> INS_SVC
    TEMPLATE_CTL --> TEMPLATE_SVC

    ASSESS_SVC --> ASSESS_VAL
    EXERCISE_SVC --> EXERCISE_VAL
    PLAN_SVC --> PLAN_VAL
    OUTCOME_SVC --> OUTCOME_VAL

    ASSESS_SVC --> DB
    EXERCISE_SVC --> DB
    PLAN_SVC --> DB
    OUTCOME_SVC --> DB
    TERMS_SVC --> FUNCTIONS
    TRANS_SVC --> DB
    INS_SVC --> DB
    TEMPLATE_SVC --> DB

    ASSESS_SVC --> DISPATCHER
    EXERCISE_SVC --> DISPATCHER
    PLAN_SVC --> DISPATCHER
    OUTCOME_SVC --> DISPATCHER

    DISPATCHER --> EVENTS

    DB --> STORED_PROC
    DB --> VIEWS
    DB --> FUNCTIONS
```

---

## Component Diagrams

### Module Component Structure

```mermaid
graph LR
    subgraph "Vietnamese PT Module"
        subgraph "src/RestControllers/VietnamesePT"
            RC1[PTAssessmentRestController]
            RC2[PTExercisePrescriptionRestController]
            RC3[PTTreatmentPlanRestController]
            RC4[PTOutcomeMeasuresRestController]
            RC5[VietnameseMedicalTermsRestController]
            RC6[VietnameseTranslationRestController]
            RC7[VietnameseInsuranceRestController]
            RC8[PTAssessmentTemplateRestController]
        end

        subgraph "src/Services/VietnamesePT"
            S1[PTAssessmentService]
            S2[PTExercisePrescriptionService]
            S3[PTTreatmentPlanService]
            S4[PTOutcomeMeasuresService]
            S5[VietnameseMedicalTermsService]
            S6[VietnameseTranslationService]
            S7[VietnameseInsuranceService]
            S8[PTAssessmentTemplateService]
        end

        subgraph "src/Validators/VietnamesePT"
            V1[PTAssessmentValidator]
            V2[PTExercisePrescriptionValidator]
            V3[PTTreatmentPlanValidator]
            V4[PTOutcomeMeasuresValidator]
        end

        subgraph "interface/forms"
            F1[vietnamese_pt_assessment]
            F2[vietnamese_pt_exercise]
            F3[vietnamese_pt_treatment_plan]
            F4[vietnamese_pt_outcome]
        end

        subgraph "library/custom"
            W1[vietnamese_pt_widget.php]
        end
    end

    RC1 --> S1
    RC2 --> S2
    RC3 --> S3
    RC4 --> S4
    RC5 --> S5
    RC6 --> S6
    RC7 --> S7
    RC8 --> S8

    S1 --> V1
    S2 --> V2
    S3 --> V3
    S4 --> V4

    F1 --> S1
    F2 --> S2
    F3 --> S3
    F4 --> S4

    W1 --> S1
    W1 --> S2
    W1 --> S3
    W1 --> S4
```

---

## Request Flow

### REST API Request Flow

```mermaid
sequenceDiagram
    participant Client
    participant Router as apis/dispatch.php
    participant Auth as OAuth2 Listener
    participant ACL as ACL Check
    participant Controller as RestController
    participant Service as Service Layer
    participant Validator
    participant EventDispatcher
    participant Database as MariaDB
    participant Response as ViewRenderer

    Client->>Router: HTTP POST /api/vietnamese-pt/assessments
    Router->>Auth: Verify OAuth2 Token
    Auth-->>Router: Token Valid
    Router->>ACL: Check ACL (patients, med)
    ACL-->>Router: Permission Granted
    Router->>Controller: PTAssessmentRestController::post()
    Controller->>Service: PTAssessmentService::insert(data)
    Service->>Validator: PTAssessmentValidator::validate(data)
    Validator-->>Service: ValidationResult

    alt Validation Failed
        Service-->>Controller: ProcessingResult (errors)
        Controller-->>Response: HTTP 400 Bad Request
        Response-->>Client: JSON Error Response
    else Validation Passed
        Service->>EventDispatcher: dispatch(BeforeAssessmentCreatedEvent)
        EventDispatcher-->>Service: Event Handled
        Service->>Database: INSERT INTO pt_assessments_bilingual
        Database-->>Service: Record Created
        Service->>EventDispatcher: dispatch(AssessmentCreatedEvent)
        EventDispatcher-->>Service: Event Handled
        Service-->>Controller: ProcessingResult (success)
        Controller-->>Response: HTTP 201 Created
        Response-->>Client: JSON Success Response
    end
```

### Traditional Form Flow

```mermaid
sequenceDiagram
    participant User as Healthcare Provider
    participant Form as interface/forms/vietnamese_pt_assessment
    participant Globals as globals.php
    participant Service as PTAssessmentService
    participant Database as MariaDB

    User->>Form: Access Assessment Form
    Form->>Globals: include globals.php
    Globals-->>Form: Initialize Session, DB, Auth
    Form->>Database: Load Existing Data (if editing)
    Database-->>Form: Return Assessment Data
    Form-->>User: Display Form (Bilingual)

    User->>Form: Submit Form Data
    Form->>Service: insert() or update()
    Service->>Database: Save to pt_assessments_bilingual
    Database-->>Service: Success
    Service-->>Form: ProcessingResult
    Form-->>User: Redirect to Patient Summary
```

---

## Service Layer Architecture

### BaseService Extension Pattern

```mermaid
classDiagram
    class BaseService {
        <<abstract>>
        #table: string
        #validator: BaseValidator
        +getOne(id): ProcessingResult
        +getAll(search): ProcessingResult
        +insert(data): ProcessingResult
        +update(id, data): ProcessingResult
        +delete(id): ProcessingResult
        #filterData(data, whitelist): array
    }

    class PTAssessmentService {
        -ASSESSMENT_TABLE: string
        -validator: PTAssessmentValidator
        +__construct()
        +getAll(search): ProcessingResult
        +getOne(id): ProcessingResult
        +insert(data): ProcessingResult
        +update(id, data): ProcessingResult
        +delete(id): ProcessingResult
        +getPatientAssessments(patientId): ProcessingResult
        +searchByVietnameseText(term, lang): ProcessingResult
    }

    class PTExercisePrescriptionService {
        -EXERCISE_TABLE: string
        -validator: PTExercisePrescriptionValidator
        +getPatientPrescriptions(patientId): ProcessingResult
        +getActiveExercises(patientId): ProcessingResult
    }

    BaseService <|-- PTAssessmentService
    BaseService <|-- PTExercisePrescriptionService
    BaseService <|-- PTTreatmentPlanService
    BaseService <|-- PTOutcomeMeasuresService
```

### Service Method Pattern

```mermaid
flowchart TD
    START[Service Method Called] --> VALIDATE{Validate Input}
    VALIDATE -->|Invalid| RETURN_ERROR[Return ProcessingResult with Errors]
    VALIDATE -->|Valid| BEFORE_EVENT[Dispatch BeforeEvent]
    BEFORE_EVENT --> DB_OP{Database Operation}
    DB_OP -->|Success| AFTER_EVENT[Dispatch AfterEvent]
    DB_OP -->|Error| CATCH[Catch Exception]
    CATCH --> RETURN_ERROR
    AFTER_EVENT --> RETURN_SUCCESS[Return ProcessingResult with Data]
    RETURN_ERROR --> END[End]
    RETURN_SUCCESS --> END
```

---

## Event System

### Event Dispatch Flow

```mermaid
sequenceDiagram
    participant Service
    participant Dispatcher as EventDispatcher
    participant Listener1 as Custom Listener 1
    participant Listener2 as Custom Listener 2
    participant Listener3 as Audit Logger

    Service->>Service: validate(data)
    Service->>Dispatcher: dispatch(BeforeAssessmentCreatedEvent)
    Dispatcher->>Listener1: handle(BeforeAssessmentCreatedEvent)
    Listener1-->>Dispatcher: Event Handled
    Dispatcher->>Listener2: handle(BeforeAssessmentCreatedEvent)
    Listener2-->>Dispatcher: Event Handled
    Dispatcher-->>Service: All Listeners Executed

    Service->>Service: Database INSERT

    Service->>Dispatcher: dispatch(AssessmentCreatedEvent)
    Dispatcher->>Listener1: handle(AssessmentCreatedEvent)
    Listener1-->>Dispatcher: Event Handled
    Dispatcher->>Listener3: handle(AssessmentCreatedEvent)
    Listener3->>Listener3: Log to EventAuditLogger
    Listener3-->>Dispatcher: Event Handled
    Dispatcher-->>Service: All Listeners Executed
```

### Available Events

```mermaid
graph TD
    subgraph "PT Assessment Events"
        BA[BeforeAssessmentCreatedEvent]
        AC[AssessmentCreatedEvent]
        BU[BeforeAssessmentUpdatedEvent]
        AU[AssessmentUpdatedEvent]
        BD[BeforeAssessmentDeletedEvent]
        AD[AssessmentDeletedEvent]
    end

    subgraph "Exercise Prescription Events"
        BEC[BeforeExerciseCreatedEvent]
        EC[ExerciseCreatedEvent]
        BEU[BeforeExerciseUpdatedEvent]
        EU[ExerciseUpdatedEvent]
    end

    subgraph "Custom Event Handlers"
        AUDIT[Audit Logging]
        NOTIF[Notifications]
        SYNC[External Sync]
        REPORT[Report Generation]
    end

    AC --> AUDIT
    AC --> NOTIF
    AC --> SYNC
    EC --> AUDIT
    EC --> REPORT
```

---

## Database Architecture

### Entity Relationship Diagram

See [DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md) for detailed ER diagram.

### Database Layer Components

```mermaid
graph TB
    subgraph "Application Layer"
        SERVICES[Service Classes]
    end

    subgraph "Database Abstraction"
        ADODB[ADODB Library]
        QUERYUTILS[QueryUtils Helper]
    end

    subgraph "Database Layer"
        TABLES[Tables<br/>pt_assessments_bilingual<br/>pt_exercise_prescriptions<br/>pt_treatment_plans<br/>pt_outcome_measures<br/>vietnamese_medical_terms<br/>vietnamese_insurance_info]
        VIEWS[Views<br/>pt_patient_summary_bilingual]
        STORED_PROC[Stored Procedures<br/>GetPatientAssessmentBilingual<br/>GetActiveExercisesBilingual]
        FUNCTIONS[Stored Functions<br/>get_vietnamese_term<br/>get_english_term]
        INDEXES[Indexes<br/>B-Tree Indexes<br/>Full-text Indexes]
    end

    SERVICES --> ADODB
    SERVICES --> QUERYUTILS
    ADODB --> TABLES
    QUERYUTILS --> TABLES
    TABLES --> VIEWS
    TABLES --> STORED_PROC
    TABLES --> FUNCTIONS
    TABLES --> INDEXES
```

### Bilingual Data Storage Pattern

```mermaid
graph LR
    subgraph "Single Record in pt_assessments_bilingual"
        ID[id: 123]
        EN_FIELD[chief_complaint_en:<br/>'Lower back pain']
        VI_FIELD[chief_complaint_vi:<br/>'Đau lưng dưới']
        LANG_PREF[language_preference:<br/>'vi']
    end

    subgraph "Application Logic"
        DISPLAY{Display Language?}
    end

    ID --> DISPLAY
    EN_FIELD --> DISPLAY
    VI_FIELD --> DISPLAY
    LANG_PREF --> DISPLAY

    DISPLAY -->|English| SHOW_EN[Show: 'Lower back pain']
    DISPLAY -->|Vietnamese| SHOW_VI[Show: 'Đau lưng dưới']
```

---

## Integration Points

### OpenEMR Core Integration

```mermaid
graph TB
    subgraph "Vietnamese PT Module"
        PT_FORMS[PT Forms]
        PT_SERVICES[PT Services]
        PT_WIDGET[PT Widget]
    end

    subgraph "OpenEMR Core"
        PATIENT[Patient Management]
        ENCOUNTER[Encounter System]
        CALENDAR[Calendar/Appointments]
        BILLING[Billing System]
        USERS[User Management]
        ACL[Access Control]
        DOCUMENTS[Document Management]
    end

    PT_FORMS --> PATIENT
    PT_FORMS --> ENCOUNTER
    PT_SERVICES --> PATIENT
    PT_SERVICES --> USERS
    PT_SERVICES --> ACL
    PT_WIDGET --> PATIENT
    PT_WIDGET --> ENCOUNTER

    PATIENT -->|Patient ID| PT_SERVICES
    ENCOUNTER -->|Encounter ID| PT_SERVICES
    USERS -->|Therapist ID| PT_SERVICES
    ACL -->|Permission Check| PT_SERVICES
```

### External System Integration

```mermaid
graph LR
    subgraph "Vietnamese PT Module"
        REST_API[REST API Endpoints]
    end

    subgraph "External Systems"
        EHR[Other EHR Systems]
        MOBILE[Mobile Apps]
        REPORTING[Reporting Systems]
        INSURANCE_SYS[Insurance Systems<br/>BHYT Integration]
    end

    REST_API <-->|JSON/REST| EHR
    REST_API <-->|JSON/REST| MOBILE
    REST_API <-->|JSON/REST| REPORTING
    REST_API <-->|JSON/REST| INSURANCE_SYS
```

---

## Security Architecture

### Authentication & Authorization Flow

```mermaid
sequenceDiagram
    participant Client
    participant OAuth2 as OAuth2 Server
    participant API as REST API
    participant ACL as ACL System
    participant Service

    Client->>OAuth2: Request Access Token<br/>(client_credentials)
    OAuth2->>OAuth2: Verify Client ID/Secret
    OAuth2-->>Client: Access Token (JWT)

    Client->>API: Request with Bearer Token
    API->>OAuth2: Validate Token
    OAuth2-->>API: Token Valid
    API->>ACL: Check Permission<br/>(section: patients, level: med)
    ACL->>ACL: Verify User ACL

    alt Permission Granted
        ACL-->>API: Access Granted
        API->>Service: Execute Request
        Service-->>API: Result
        API-->>Client: HTTP 200 OK
    else Permission Denied
        ACL-->>API: Access Denied
        API-->>Client: HTTP 403 Forbidden
    end
```

### Data Security Layers

```mermaid
graph TB
    subgraph "Application Security"
        INPUT_VAL[Input Validation<br/>Validators]
        CSRF[CSRF Protection]
        XSS[XSS Prevention<br/>Output Encoding]
        SQL_INJ[SQL Injection Prevention<br/>Prepared Statements]
    end

    subgraph "Access Control"
        OAUTH[OAuth2 Authentication]
        ACL_CHECK[ACL Authorization<br/>patients/med]
        SESSION[Session Management]
    end

    subgraph "Data Security"
        ENCRYPTION[Data Encryption at Rest]
        TLS[TLS/HTTPS in Transit]
        AUDIT[Audit Logging<br/>EventAuditLogger]
    end

    subgraph "Database Security"
        DB_USER[Limited DB User Permissions]
        COLLATION[utf8mb4_vietnamese_ci<br/>Character Set Security]
        BACKUP[Encrypted Backups]
    end

    INPUT_VAL --> SQL_INJ
    OAUTH --> ACL_CHECK
    ACL_CHECK --> SESSION
    ENCRYPTION --> TLS
    TLS --> AUDIT
    DB_USER --> COLLATION
    COLLATION --> BACKUP
```

---

## Deployment Architecture

### Development Environment

```mermaid
graph TB
    subgraph "Developer Workstation"
        CODE[Local PHP Code<br/>/home/dang/dev/openemr]
        IDE[IDE/Editor]
    end

    subgraph "Docker Containers"
        MARIADB[MariaDB Container<br/>Port 3306<br/>utf8mb4_vietnamese_ci]
        PHPMYADMIN[phpMyAdmin<br/>Port 8081]
        ADMINER[Adminer<br/>Port 8082]
        REDIS[Redis Cache<br/>Port 6379]
        MAILHOG[MailHog<br/>Port 8025]
    end

    subgraph "Shared Volumes"
        DB_DATA[Database Data<br/>pt-mariadb-data]
        CONFIG[Configs<br/>init scripts]
    end

    CODE --> MARIADB
    CODE --> REDIS
    IDE --> CODE
    MARIADB --> DB_DATA
    MARIADB --> CONFIG
    PHPMYADMIN --> MARIADB
    ADMINER --> MARIADB
```

### Production Architecture

```mermaid
graph TB
    subgraph "Load Balancer"
        LB[Nginx/HAProxy]
    end

    subgraph "Web Servers"
        WEB1[OpenEMR Instance 1]
        WEB2[OpenEMR Instance 2]
        WEB3[OpenEMR Instance N]
    end

    subgraph "Database Cluster"
        MASTER[MariaDB Master<br/>Read/Write]
        REPLICA1[MariaDB Replica 1<br/>Read Only]
        REPLICA2[MariaDB Replica 2<br/>Read Only]
    end

    subgraph "Caching Layer"
        REDIS_CLUSTER[Redis Cluster]
    end

    subgraph "File Storage"
        NFS[Shared NFS Storage<br/>Documents/Images]
    end

    subgraph "Monitoring"
        LOGS[Centralized Logging]
        METRICS[Metrics/Monitoring]
    end

    LB --> WEB1
    LB --> WEB2
    LB --> WEB3

    WEB1 --> MASTER
    WEB2 --> MASTER
    WEB3 --> MASTER

    WEB1 --> REPLICA1
    WEB2 --> REPLICA2
    WEB3 --> REPLICA1

    WEB1 --> REDIS_CLUSTER
    WEB2 --> REDIS_CLUSTER
    WEB3 --> REDIS_CLUSTER

    WEB1 --> NFS
    WEB2 --> NFS
    WEB3 --> NFS

    MASTER --> REPLICA1
    MASTER --> REPLICA2

    WEB1 --> LOGS
    WEB2 --> LOGS
    WEB3 --> LOGS
    MASTER --> METRICS
```

---

## Performance Optimization

### Caching Strategy

```mermaid
graph LR
    subgraph "Request Flow"
        REQUEST[API Request]
        CACHE_CHECK{Cache Hit?}
        CACHE[Redis Cache]
        DB[Database Query]
        RESPONSE[Response]
    end

    REQUEST --> CACHE_CHECK
    CACHE_CHECK -->|Yes| CACHE
    CACHE_CHECK -->|No| DB
    CACHE --> RESPONSE
    DB -->|Store| CACHE
    DB --> RESPONSE
```

### Database Indexing Strategy

All Vietnamese PT tables include:
- **Primary Key Indexes**: Fast lookups by ID
- **Foreign Key Indexes**: Join optimization (patient_id, encounter_id, therapist_id)
- **Status/Date Indexes**: Query filtering
- **Full-text Indexes**: Bilingual text search on EN/VI fields
- **Composite Indexes**: Multi-column query optimization

---

## Technology Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Backend** | PHP 8.2+ | Application logic |
| **Framework** | Symfony Components | Event system, validation |
| **API** | REST (JSON) | External interface |
| **Database** | MariaDB 10.11+ | Data persistence |
| **Collation** | utf8mb4_vietnamese_ci | Vietnamese character support |
| **Caching** | Redis | Performance optimization |
| **Auth** | OAuth2 | API authentication |
| **ACL** | OpenEMR ACL System | Authorization |
| **Frontend** | Bootstrap 4, jQuery | UI components |

---

## File Structure

```
openemr/
├── src/
│   ├── RestControllers/VietnamesePT/     # 8 REST controllers
│   ├── Services/VietnamesePT/            # 8 service classes
│   └── Validators/VietnamesePT/          # 4 validators
├── interface/forms/
│   ├── vietnamese_pt_assessment/         # Assessment form
│   ├── vietnamese_pt_exercise/           # Exercise form
│   ├── vietnamese_pt_treatment_plan/     # Treatment plan form
│   └── vietnamese_pt_outcome/            # Outcome measures form
├── library/custom/
│   └── vietnamese_pt_widget.php          # Patient summary widget
├── apis/routes/
│   └── _rest_routes_standard.inc.php     # PT API routes (lines 7293-7581)
├── sql/
│   └── vietnamese_pt_functions.sql       # Database functions
├── docker/development-physiotherapy/
│   └── configs/mariadb/init/
│       ├── 02-pt-bilingual-schema.sql    # Table definitions
│       └── 04-physiotherapy-extensions.sql # Extensions
├── tests/Tests/
│   ├── Services/Vietnamese/              # Service tests
│   └── Vietnamese/                       # Integration tests
└── Documentation/physiotherapy/          # This documentation
```

---

## Related Documentation

- **[Database Schema](./DATABASE_SCHEMA.md)** - Detailed database structure
- **[API Reference](../development/API_REFERENCE.md)** - API endpoint documentation
- **[Production Deployment](./PRODUCTION_DEPLOYMENT.md)** - Deployment guide
- **[Development Guide](../development/HYBRID_DEVELOPMENT_GUIDE.md)** - Developer setup

---

**Architecture Documentation Version:** 1.0
**Last Updated:** 2025-11-22
**Maintainer:** Dang Tran <tqvdang@msn.com>
