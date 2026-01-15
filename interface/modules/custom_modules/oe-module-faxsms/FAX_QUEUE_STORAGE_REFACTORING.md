# FAX Queue Storage Refactoring

## Overview

This document describes the comprehensive refactoring of the OpenEMR FaxSMS module to standardize inbound fax storage across all fax providers (EtherFax and SignalWire). The refactoring ensures consistent queue storage, automatic patient matching, and proper document integration.

## Problem Statement

Before this refactoring, the module had inconsistent approaches to storing inbound faxes:

- **SignalWireClient** stored faxes directly to temporary directories without proper document integration
- **EtherFaxActions** used deprecated database functions and didn't leverage FaxDocumentService
- **Database schema** was missing critical columns (status, direction, site_id, patient_id, document_id, media_path)
- **No automatic patient matching** for received faxes across either provider
- **Inconsistent data structures** in details_json between providers

## Solution Architecture

### Core Components

#### 1. FaxDocumentService
The centralized service for managing fax documents:

```
FaxDocumentService
├── storeFaxDocument()          # Store fax with automatic patient matching
├── assignFaxToPatient()        # Assign unassigned fax to patient
├── findPatientByPhone()        # Auto-match patient by phone number
├── getUnassignedFaxes()        # List unassigned received faxes
├── getFaxDocument()            # Retrieve fax details
└── deleteFaxDocument()         # Mark fax as deleted
```

**Key Features:**
- Automatic patient matching by phone number (multiple format patterns)
- Stores documents in OpenEMR document system with FAX category
- Separates unassigned faxes in dedicated directory
- Handles both patient-assigned and unassigned documents
- Returns document_id and media_path for queue tracking

#### 2. Queue Storage Pattern

Both EtherFax and SignalWire now follow the same storage pattern:

```
Incoming Fax
    ↓
[Download/Retrieve Media]
    ↓
[Attempt Patient Matching by Phone]
    ↓
[Store Document via FaxDocumentService]
    ↓
[Insert/Update oe_faxsms_queue]
    ├── job_id (provider SID)
    ├── status (received, delivered, failed, etc.)
    ├── direction (inbound)
    ├── patient_id (if matched)
    ├── document_id (OpenEMR document)
    ├── media_path (stored file location)
    └── details_json (complete metadata)
```

#### 3. Database Schema Updates

The `oe_faxsms_queue` table now includes:

| Column | Type | Purpose |
|--------|------|---------|
| id | int | Primary key |
| job_id | text | Provider's fax ID/SID |
| status | varchar(50) | Fax status (received, queued, delivered, failed) |
| direction | varchar(20) | inbound or outbound |
| site_id | varchar(63) | Multi-site support |
| patient_id | int | Assigned patient (if matched) |
| document_id | int | OpenEMR document reference |
| media_path | longtext | File system path to fax media |
| details_json | longtext | Complete fax metadata |
| calling_number | tinytext | Sender's phone number |
| called_number | tinytext | Recipient's phone number |
| mime | tinytext | MIME type of document |
| date | datetime | Queue record creation time |
| receive_date | datetime | Fax received time |
| deleted | int | Soft delete flag |
| uid | int | User ID who processed fax |
| account | tinytext | Provider account identifier |

**Key Indexes:**
- `job_id` - Fast lookup by provider SID
- `site_id` - Multi-site filtering
- `patient_id` - Patient-based queries
- `uid`, `receive_date` - User-based timeline queries

## Implementation Details

### SignalWireClient Refactoring

#### New Method: storeInboundFax()

Handles standardized inbound fax storage:

```php
private function storeInboundFax(array $faxData): void
{
    // 1. Download media using oeHttp with Bearer token
    $mediaContent = $this->downloadFaxMediaContent($mediaUrl);
    
    // 2. Initialize FaxDocumentService
    $faxService = new FaxDocumentService($siteId);
    
    // 3. Attempt patient matching by phone
    $patientId = $faxService->findPatientByPhone($fromNumber);
    
    // 4. Store document
    $result = $faxService->storeFaxDocument(
        $faxSid,
        $mediaContent,
        $fromNumber,
        $patientId,
        $mimeType
    );
    
    // 5. Insert/update queue record
    QueryUtils::sqlStatementThrowException($sql, [
        // ... parameters with document_id and media_path
    ]);
}
```

#### New Method: downloadFaxMediaContent()

Secure media download with proper authentication:

```php
private function downloadFaxMediaContent(string $mediaUrl): ?string
{
    // 1. Validate URL (SSRF protection)
    if (!$this->isValidSignalWireUrl($mediaUrl)) {
        return null;
    }
    
    // 2. Get and decrypt credentials
    $apiToken = $this->getDecryptedApiToken();
    
    // 3. Download using oeHttp with Bearer token
    $httpRequest = oeHttpRequest::newArgs(oeHttp::client());
    $httpRequest->usingHeaders([
        'Authorization' => 'Bearer ' . $apiToken
    ]);
    $response = $httpRequest->get($mediaUrl);
    
    return $response->body();
}
```

#### Security: isValidSignalWireUrl()

Prevents SSRF attacks by whitelisting SignalWire domains:

```php
private function isValidSignalWireUrl(string $url): bool
{
    $parsedUrl = parse_url($url);
    
    // Only HTTPS
    if ($parsedUrl['scheme'] !== 'https') {
        return false;
    }
    
    // Whitelist SignalWire domains
    $allowedDomains = [
        'files.signalwire.com',
        'api.signalwire.com'
    ];
    
    // Check host
    $host = strtolower($parsedUrl['host']);
    foreach ($allowedDomains as $domain) {
        if ($host === $domain || str_ends_with($host, '.' . $domain)) {
            return true;
        }
    }
    
    return false;
}
```

#### Updated: sendFax()

Outbound faxes now stored in queue after successful send:

```php
$fax = $this->client->fax->v1->faxes->create([
    'to' => $phone,
    'from' => $this->faxNumber,
    'mediaUrl' => $mediaUrl
]);

// Build metadata
$faxData = [
    'sid' => $fax->sid,
    'from' => $this->faxNumber,
    'to' => $phone,
    'direction' => 'outbound',
    'status' => $fax->status ?? 'queued',
    'recipient_name' => $recipientName,
    'sent_by' => $user['username'],
    'dateCreated' => date('Y-m-d H:i:s')
];

// Store in queue
QueryUtils::sqlStatementThrowException($sql, [
    $uid, $fax->sid, $this->faxNumber, $phone,
    json_encode($faxData), 'outbound', $fax->status ?? 'queued', $siteId
]);
```

#### Refactored: upsertFaxFromSignalWire()

Simplified to only handle inbound faxes:

```php
private function upsertFaxFromSignalWire($fax): void
{
    // Only process inbound faxes
    if ($fax->direction !== 'inbound') {
        return;
    }
    
    // Fetch fresh status from API
    $freshFax = $this->client->fax->v1->faxes->getContext($jobId)->fetch();
    
    // Build standardized fax data
    $faxData = [
        'sid' => $jobId,
        'from' => $from,
        'to' => $to,
        'status' => $status,
        'direction' => 'inbound',
        'numPages' => $numPages,
        'mediaUrl' => $mediaUrl,
        'mimeType' => 'application/pdf'
    ];
    
    // Use standardized storage method
    $this->storeInboundFax($faxData);
}
```

### EtherFaxActions Refactoring

#### Updated: insertFaxQueue()

Now uses FaxDocumentService for consistent handling:

```php
public function insertFaxQueue($faxDetails): int
{
    try {
        // 1. Decode fax content
        $mediaContent = base64_decode((string)$faxDetails->FaxImage);
        
        // 2. Initialize FaxDocumentService
        $faxService = new FaxDocumentService($siteId);
        
        // 3. Auto-match patient
        $patientId = $faxService->findPatientByPhone($fromNumber);
        
        // 4. Store document
        $result = $faxService->storeFaxDocument(
            $jobId,
            $mediaContent,
            $fromNumber,
            $patientId,
            $docType
        );
        
        // 5. Insert queue record with document references
        QueryUtils::sqlStatementThrowException($sql, [
            $uid, $account, $jobId, $received,
            $fromNumber, $toNumber, $docType,
            json_encode($faxData),
            'received', 'inbound',
            $siteId, $patientId, $documentId, $mediaPath
        ]);
        
        return (int)$recordId;
    } catch (Exception $e) {
        error_log("EtherFaxActions.insertFaxQueue(): ERROR - " . $e->getMessage());
        throw $e;
    }
}
```

#### Database Function Updates

All deprecated database functions replaced with QueryUtils:

| Old Function | New Method | Location |
|--------------|------------|----------|
| sqlStatement | QueryUtils::fetchRecords() | getNotificationLog, fetchFaxQueue |
| sqlFetchArray | (replaced with foreach) | getNotificationLog, fetchFaxQueue |
| sqlQuery | QueryUtils::querySingleRow() | getUser, getAssumedPatientId, fetchFaxFromQueue, fetchQueueCount, setFaxDeleted |
| sqlInsert | QueryUtils::sqlStatementThrowException() | insertFaxQueue, insertSentFaxQueue |

### Multi-Site Support

All queries now filter by site_id:

```php
$siteId = $_SESSION['site_id'] ?? 'default';

// Query includes site_id filter
$result = QueryUtils::querySingleRow(
    "SELECT * FROM oe_faxsms_queue WHERE job_id = ? AND site_id = ?",
    [$jobId, $siteId]
);
```

## Patient Matching Algorithm

FaxDocumentService::findPatientByPhone() attempts to match patients using multiple phone number patterns:

```
Input Phone: +1 (555) 123-4567

Patterns Tried (in order):
1. 5551234567 (digits only)
2. +15551234567 (E.164 format)
3. 15551234567 (with country code)
4. 555-123-4567 (formatted)
5. (555) 123-4567 (formatted with parens)

Database Search:
- phone_cell LIKE '%pattern%'
- phone_home LIKE '%pattern%'
- phone_biz LIKE '%pattern%'

Returns: Patient ID if found, 0 otherwise
```

## Error Handling and Logging

### Exception Handling

```php
try {
    // Business logic
} catch (FaxDocumentException $e) {
    error_log("Service: Error - " . $e->getMessage());
    throw $e;  // Propagate or handle gracefully
} catch (Exception $e) {
    error_log("Service: Unexpected error - " . $e->getMessage());
    // Continue with queue insert even if document storage fails
}
```

### Logging Strategy

All operations log key details:

```
INFO: "Successfully stored fax {jobId} (patient_id={pid}, document_id={docId})"
WARN: "Failed to download media for fax {jobId}"
ERROR: "insertFaxQueue(): ERROR - {message}"
DEBUG: "Processing fax sid={sid}, from={from}, direction={direction}"
```

## Migration Path

For existing installations, the schema changes are non-breaking:

1. **CREATE TABLE IF NOT EXISTS** - No effect if table exists
2. **#IfMissingColumn ALTER TABLE** - Only add columns if absent
3. **Default Values** - Existing records get NULL or default values
4. **Backward Compatible** - Old code continues to work

Run migration:
```bash
# The module installer automatically applies SQL migrations
# Or manually:
mysql -u user -p database < table.sql
```

## Testing Checklist

### Inbound Fax Storage

- [ ] Receive fax from EtherFax → stored in queue
- [ ] Receive fax from SignalWire → stored in queue
- [ ] Fax auto-matches patient by phone number
- [ ] Document created in OpenEMR document system
- [ ] document_id and media_path saved in queue
- [ ] Unassigned fax stored correctly if no patient match

### Outbound Fax Storage

- [ ] Send fax via SignalWire → stored in queue
- [ ] Outbound record has direction='outbound'
- [ ] Status updated after completion
- [ ] Recipient and sender info captured in details_json

### Patient Matching

- [ ] Phone number with country code matches patient
- [ ] Formatted phone numbers match
- [ ] Multiple pattern formats tested
- [ ] No match returns patient_id = 0 (unassigned)

### Multi-Site Support

- [ ] Queue queries filter by site_id
- [ ] Different sites see only their faxes
- [ ] Document storage scoped to site

### Security

- [ ] SSRF protection validates URLs
- [ ] Bearer token authentication used
- [ ] Credentials encrypted in database
- [ ] Input validation on all user inputs

## Performance Considerations

### Database Indexes

```sql
KEY `job_id` (`job_id`(255)) -- Fast SID lookup
KEY `site_id` (`site_id`) -- Multi-site filtering
KEY `patient_id` (`patient_id`) -- Patient-based queries
KEY `uid` (`uid`,`receive_date`) -- User timeline
```

### Media Storage

- **Small files** (< 50MB): Stored in OpenEMR document system
- **Large files**: Consider external storage integration
- **Cleanup**: Soft delete (deleted=1) for audit trail

### Query Optimization

- Use indexed columns (job_id, site_id, patient_id)
- Avoid full table scans with LIMIT clauses
- Consider archiving old records

## Future Enhancements

1. **Webhook Receiver Integration**: Full webhook support for real-time notifications
2. **Bulk Patient Assignment**: UI for assigning multiple unmatched faxes
3. **Custom Routing Rules**: Route faxes based on content patterns
4. **Document OCR**: Extract text from fax images
5. **Archive Management**: Automated deletion of old records
6. **Fax Status Tracking**: Real-time status updates from providers
7. **Custom Metadata Fields**: Extend details_json with business rules

## References

- [FaxDocumentService](./src/Controller/FaxDocumentService.php) - Document management service
- [SignalWireClient](./src/Controller/SignalWireClient.php) - SignalWire provider implementation
- [EtherFaxActions](./src/Controller/EtherFaxActions.php) - EtherFax provider implementation
- [Database Schema](./table.sql) - Queue table definition
- [Webhook Receiver](./library/webhook_receiver.php) - Webhook event handler
- [Module Configuration](./src/Controller/ModuleManager.php) - Setup and configuration

## Code Quality Standards

All code follows:

- **PSR-12**: PHP Standards Recommendations for Extended Coding Style
- **OpenEMR Standards**: Following module development guidelines
- **QueryUtils**: Using OpenEMR database abstraction layer
- **Exception Handling**: Custom exceptions for clear error scoping
- **Error Logging**: Comprehensive logging for debugging and auditing

## Support and Troubleshooting

### Common Issues

**Issue**: Faxes not matching to patients
- Check phone number formats in patient_data table
- Verify phone_cell, phone_home, phone_biz are populated
- Test findPatientByPhone() with various phone formats

**Issue**: Documents not created
- Check FAX category exists in categories table
- Verify temporary_files_dir has write permissions
- Review error logs for FaxDocumentException messages

**Issue**: Multi-site faxes mixed up
- Verify $_SESSION['site_id'] is set correctly
- Check site_id filtering in all queries
- Review table.sql ALTER statements applied

### Debug Logging

Enable detailed logging:

```php
error_log("DEBUG: storeInboundFax() - Processing fax {$faxSid}");
error_log("DEBUG: downloadFaxMediaContent() - Downloaded " . strlen($content) . " bytes");
error_log("DEBUG: Patient match result: {$patientId}");
error_log("DEBUG: Document created: {$documentId}");
```

## Contributors

- **Refactoring**: Warp AI Agent
- **Original Code**: Jerry Padgett, SignalWire Integration Team
- **Testing**: QA Team

## License

GNU General Public License v3 (GPL-3.0)

See [LICENSE](../../../../../../LICENSE) for details.
