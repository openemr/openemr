# Vietnamese PT Module - Load Test Scenarios

**AI-GENERATED DOCUMENTATION**

This document describes load testing scenarios for the Vietnamese Physiotherapy module using Apache JMeter or similar tools.

## Overview

Load tests validate system performance under various user loads and ensure the Vietnamese PT module can handle production traffic patterns.

## Test Environment Requirements

- OpenEMR instance with Vietnamese PT module installed
- Test database with sample data (100+ patients, 500+ PT records)
- JMeter 5.5+ or equivalent load testing tool
- Vietnamese language support enabled

## Scenario 1: 10 Concurrent Users (Light Load)

**Duration:** 15 minutes
**Ramp-up Period:** 2 minutes
**Target:** Simulate typical clinic usage

### Test Flow:
1. **Login** (1 request/user)
   - Authenticate via OAuth2
   - Expected response time: < 500ms

2. **Patient Search** (5 requests/user)
   - Search for patients by name (English and Vietnamese)
   - Expected response time: < 200ms

3. **View PT Assessment** (3 requests/user)
   - GET `/apis/default/vietnamese-pt/assessment?patient_id={id}`
   - Expected response time: < 150ms

4. **Create Exercise Prescription** (2 requests/user)
   - POST `/apis/default/vietnamese-pt/exercise`
   - Expected response time: < 300ms

5. **View Outcome Measures** (2 requests/user)
   - GET `/apis/default/vietnamese-pt/outcome?patient_id={id}`
   - Expected response time: < 150ms

### Success Criteria:
- 99% of requests complete successfully (HTTP 200/201)
- Average response time < 250ms
- No database connection errors
- Memory usage stable (< 2GB)

### JMeter Configuration:
```xml
<ThreadGroup guiclass="ThreadGroupGui" testclass="ThreadGroup" testname="10 Users Light Load">
  <stringProp name="ThreadGroup.num_threads">10</stringProp>
  <stringProp name="ThreadGroup.ramp_time">120</stringProp>
  <stringProp name="ThreadGroup.duration">900</stringProp>
  <boolProp name="ThreadGroup.scheduler">true</boolProp>
</ThreadGroup>
```

## Scenario 2: 100 Concurrent Users (Heavy Load)

**Duration:** 30 minutes
**Ramp-up Period:** 5 minutes
**Target:** Simulate peak clinic hours

### Test Flow:
1. **Login** (1 request/user)
   - Expected response time: < 1000ms

2. **Mixed Operations** (20 requests/user):
   - 40% Read operations (GET assessments, exercises, outcomes)
   - 40% Create operations (POST new records)
   - 15% Update operations (PUT existing records)
   - 5% Delete operations (DELETE old records)

3. **Vietnamese Text Search** (5 requests/user)
   - Search using Vietnamese characters
   - Expected response time: < 500ms

4. **Medical Terms Translation** (3 requests/user)
   - POST `/apis/default/vietnamese-pt/translation`
   - Expected response time: < 200ms

### Success Criteria:
- 95% of requests complete successfully
- Average response time < 500ms
- Peak response time < 2000ms
- Error rate < 5%
- Database connection pool handles load efficiently

### JMeter Configuration:
```xml
<ThreadGroup guiclass="ThreadGroupGui" testclass="ThreadGroup" testname="100 Users Heavy Load">
  <stringProp name="ThreadGroup.num_threads">100</stringProp>
  <stringProp name="ThreadGroup.ramp_time">300</stringProp>
  <stringProp name="ThreadGroup.duration">1800</stringProp>
  <boolProp name="ThreadGroup.scheduler">true</boolProp>
</ThreadGroup>
```

## Scenario 3: Sustained Load (Endurance Test)

**Duration:** 1 hour
**Concurrent Users:** 25
**Ramp-up Period:** 3 minutes
**Target:** Validate stability over time

### Test Flow:
Continuous cycling through all PT module operations:
- Patient creation and PT assessment
- Exercise prescription workflows
- Treatment plan management
- Outcome measure tracking
- Report generation

### Success Criteria:
- No memory leaks (memory usage remains stable)
- No performance degradation over time
- Database connection pool remains healthy
- No resource exhaustion errors
- Response times remain consistent

### Monitoring Points:
- CPU usage
- Memory usage (heap and non-heap)
- Database connection count
- Response time trends
- Error rate trends

## Scenario 4: Peak Load Simulation

**Duration:** 10 minutes
**Concurrent Users:** 200
**Ramp-up Period:** 2 minutes
**Target:** Test system limits

### Test Flow:
Aggressive mixed operations simulating system stress:
- Rapid form submissions
- Concurrent database writes
- Vietnamese text searches
- Report generation
- Translation requests

### Success Criteria:
- System remains responsive (no timeouts)
- Error rate < 10%
- No database deadlocks
- Graceful degradation if limits reached

## Vietnamese-Specific Test Cases

### Vietnamese Character Handling
Test Vietnamese text in all fields:
- Tone marks (á, à, ả, ã, ạ)
- Special characters (đ, Đ)
- Combined characters (ơ, ư, ô, â)

Expected: No character corruption, proper sorting/searching

### Bilingual Data Operations
Test mixed English/Vietnamese operations:
- Create record in English, retrieve in Vietnamese
- Search using Vietnamese, display English
- Translation accuracy under load

### Database Collation Performance
Test utf8mb4_vietnamese_ci collation performance:
- Case-insensitive searches
- Vietnamese alphabetical sorting
- Full-text search with Vietnamese

## Sample JMeter Test Plan Structure

```
Vietnamese PT Load Test.jmx
├── Test Plan
│   ├── User Defined Variables
│   │   ├── BASE_URL = ${__P(base_url,http://localhost:8300)}
│   │   ├── API_BASE = /apis/default/vietnamese-pt
│   │   └── THREADS = ${__P(threads,10)}
│   │
│   ├── Setup Thread Group
│   │   └── HTTP Request: OAuth2 Authentication
│   │
│   ├── Main Thread Group (10/100 users)
│   │   ├── HTTP Request: GET /assessment
│   │   ├── HTTP Request: POST /assessment
│   │   ├── HTTP Request: GET /exercise
│   │   ├── HTTP Request: POST /exercise
│   │   ├── HTTP Request: GET /treatment-plan
│   │   └── HTTP Request: POST /outcome
│   │
│   ├── Listeners
│   │   ├── Aggregate Report
│   │   ├── View Results Tree
│   │   └── Response Time Graph
│   │
│   └── Teardown Thread Group
│       └── HTTP Request: Cleanup Test Data
```

## Running Load Tests

### Using JMeter:
```bash
# Run light load test
jmeter -n -t vietnamese-pt-10-users.jmx -l results-10users.jtl -e -o report-10users/

# Run heavy load test
jmeter -n -t vietnamese-pt-100-users.jmx -l results-100users.jtl -e -o report-100users/

# Run with custom parameters
jmeter -n -t vietnamese-pt-load.jmx \
  -Jbase_url=http://openemr.test \
  -Jthreads=50 \
  -Jduration=1800 \
  -l results.jtl
```

### Using Apache Bench (ab):
```bash
# Simple endpoint test
ab -n 1000 -c 10 -H "Authorization: Bearer {token}" \
  http://localhost:8300/apis/default/vietnamese-pt/assessment

# POST test with Vietnamese data
ab -n 100 -c 5 -p assessment-data.json -T application/json \
  -H "Authorization: Bearer {token}" \
  http://localhost:8300/apis/default/vietnamese-pt/assessment
```

## Performance Baselines

Based on typical hardware (4 CPU, 8GB RAM, SSD):

| Operation | Expected Response Time | Threshold |
|-----------|----------------------|-----------|
| GET single record | 50-100ms | 200ms |
| GET list (10 records) | 100-150ms | 300ms |
| POST create | 100-200ms | 400ms |
| PUT update | 100-200ms | 400ms |
| DELETE | 50-100ms | 200ms |
| Vietnamese search | 150-250ms | 500ms |
| Translation | 100-150ms | 300ms |

## Troubleshooting Performance Issues

### Common Bottlenecks:
1. **Database queries**
   - Check slow query log
   - Verify indexes on Vietnamese columns
   - Review connection pool settings

2. **Vietnamese text processing**
   - Ensure utf8mb4 charset throughout
   - Verify collation on text columns
   - Check full-text search indexes

3. **Memory usage**
   - Monitor PHP memory_limit
   - Check for memory leaks in services
   - Review result set sizes

4. **API authentication**
   - Verify OAuth2 token caching
   - Check session management
   - Review ACL query performance

## Continuous Monitoring

Recommended monitoring during production:

- APM tool (New Relic, DataDog, etc.)
- Database query performance
- API endpoint response times
- Error rates and types
- Vietnamese character handling

---

**AI-GENERATED DOCUMENTATION - END**
