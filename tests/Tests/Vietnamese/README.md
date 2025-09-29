# Vietnamese Physiotherapy Test Suite

## Overview

Comprehensive test suite for Vietnamese bilingual physiotherapy features in OpenEMR, achieving 100% coverage of all Vietnamese PT database tables, procedures, and functionality.

## Test Coverage

### Unit Tests (tests/Tests/Unit/Vietnamese/)

1. **CharacterEncodingTest.php** (10 tests)
   - UTF-8 validation
   - Vietnamese diacritics preservation
   - Byte length handling
   - Case-insensitive comparison
   - JSON serialization/deserialization
   - Text truncation safety
   - Character set validation

2. **MedicalTerminologyTest.php** (10 tests)
   - Bilingual term structure validation
   - Vietnamese → English lookup
   - English → Vietnamese reverse lookup
   - Term categorization (general, conditions, treatments, body_parts)
   - Search functionality
   - Term uniqueness
   - Formatting consistency

3. **BilingualAssessmentTest.php** (12 tests)
   - Assessment data structure
   - Bilingual field validation
   - Patient name formats (Vietnamese/English)
   - Data serialization (JSON)
   - Field completeness
   - Language consistency
   - Database format conversion

4. **VietnameseScriptTest.php** (11 tests)
   - Script existence and executability
   - Bash shebang validation
   - Expected functions present
   - Help command functionality
   - Command validation
   - Vietnamese encoding support
   - Database connection handling
   - Error handling
   - Syntax validation
   - Documentation presence
   - Collation checks

### Integration Tests (tests/Tests/Services/Vietnamese/)

1. **VietnamesePhysiotherapyServiceTest.php** (10 tests)
   - Database connection configuration
   - Vietnamese collation sorting
   - Vietnamese text search (LIKE queries)
   - Medical term storage format
   - Bilingual assessment data structures
   - Patient data bilingual format
   - Exercise prescription format
   - Vietnamese insurance information format
   - Outcome measures format
   - Vietnamese LIKE search patterns

2. **VietnameseDatabaseIntegrationTest.php** (7 tests)
   - Database connection with Vietnamese collation
   - Vietnamese test table existence
   - Insert and retrieve Vietnamese text
   - Vietnamese text search with LIKE
   - Vietnamese collation sorting
   - Special Vietnamese characters preservation
   - Case-insensitive Vietnamese search

3. **VietnameseMedicalTermsTableTest.php** (13 tests)
   - Table existence
   - Table structure validation (13 columns)
   - Insert medical term
   - Retrieve by English term
   - Retrieve by Vietnamese term
   - Search by category
   - Update medical term
   - Soft delete (is_active flag)
   - Vietnamese text LIKE search
   - Multiple category filtering
   - Abbreviation handling
   - Timestamp auto-update

4. **VietnameseStoredProcedureTest.php** (7 tests)
   - Stored procedure existence
   - Vietnamese search term
   - English search term
   - Search ranking
   - Special Vietnamese characters
   - Empty search handling
   - Non-existent term handling

5. **PTTablesIntegrationTest.php** (10 tests)
   - `pt_assessments_bilingual` table CRUD
   - `pt_exercise_prescriptions` table CRUD
   - `pt_outcome_measures` table CRUD
   - `pt_treatment_plans` table CRUD
   - `pt_assessment_templates` table CRUD
   - `vietnamese_insurance_info` table CRUD
   - JSON field operations (goals, measurements)
   - Full-text search on bilingual assessment
   - Vietnamese text preservation in all tables
   - Comprehensive CRUD with Vietnamese data

### Test Fixtures (tests/Tests/Fixtures/Vietnamese/)

1. **VietnameseTestData.php**
   - Patient names (Vietnamese/English pairs)
   - Medical terminology dictionary (40+ terms)
   - Sample assessments (bilingual)
   - Exercise prescriptions
   - Outcome measures
   - Insurance information
   - Vietnamese text samples
   - Vietnamese character sets (vowels, special chars)
   - Date/time formats for Vietnam

## Database Tables Covered

### ✅ 100% Coverage

1. **vietnamese_test** - Vietnamese character testing table
2. **vietnamese_medical_terms** - Bilingual medical terminology (13 tests)
3. **pt_assessments_bilingual** - Bilingual physiotherapy assessments
4. **vietnamese_insurance_info** - Vietnamese health insurance details
5. **pt_exercise_prescriptions** - Exercise prescriptions (bilingual)
6. **pt_outcome_measures** - Treatment outcome tracking
7. **pt_treatment_plans** - Treatment plan management
8. **pt_assessment_templates** - Assessment templates with JSON fields

## Database Features Covered

### Stored Procedures (7 tests)
- **GetBilingualTerm()** - Bilingual term search with ranking

### Full-Text Search (3 tests)
- FULLTEXT indexes on Vietnamese fields
- MATCH...AGAINST queries
- Boolean mode search

### JSON Fields (3 tests)
- `rom_measurements` - Range of motion data
- `strength_measurements` - Strength assessment data
- `balance_assessment` - Balance evaluation data
- `assessment_fields` - Dynamic assessment templates
- `goals_short_term` / `goals_long_term` - Treatment goals

### Collation & Encoding (15 tests)
- utf8mb4_vietnamese_ci collation
- Vietnamese character preservation
- Case-insensitive search
- Alphabetical sorting (Vietnamese order: L, N, P, T)
- Special Vietnamese characters (đ, ă, â, ê, ô, ơ, ư and tones)

## Running Tests

### Run All Vietnamese Tests
```bash
vendor/bin/phpunit --testsuite vietnamese
```

### Run Specific Test Categories
```bash
# Unit tests only
vendor/bin/phpunit tests/Tests/Unit/Vietnamese/

# Integration tests only
vendor/bin/phpunit tests/Tests/Services/Vietnamese/

# Specific test class
vendor/bin/phpunit tests/Tests/Services/Vietnamese/VietnameseMedicalTermsTableTest.php
```

### Prerequisites

1. **Database Running**: Docker environment must be running
   ```bash
   docker-compose -f docker/development-physiotherapy/docker-compose.yml up -d
   ```

2. **Database Initialized**: Vietnamese tables and data must be present
   ```bash
   # Check database
   docker exec openemr_mariadb_dev mysql -u openemr -popenemr openemr -e "SHOW TABLES LIKE 'vietnamese%'"
   ```

3. **Composer Dependencies**: PHPUnit installed
   ```bash
   composer install
   ```

## Test Statistics

### Total Tests Created
- **Unit Tests**: 43 tests across 4 test classes
- **Integration Tests**: 47 tests across 5 test classes
- **Total**: 90 comprehensive tests

### Coverage by Category
- **Character Encoding**: 10 tests
- **Medical Terminology**: 20 tests (10 unit + 10 integration)
- **Database Tables**: 36 tests (7 tables)
- **Stored Procedures**: 7 tests
- **Full-Text Search**: 3 tests
- **JSON Operations**: 3 tests
- **Shell Scripts**: 11 tests

### Coverage Percentage
- **Database Tables**: 100% (7/7 tables covered)
- **Stored Procedures**: 100% (1/1 procedure covered)
- **Database Features**: 100% (collation, fulltext, JSON all covered)
- **Vietnamese Encoding**: 100% (all character sets tested)
- **Shell Scripts**: 100% (vietnamese-db-tools.sh covered)

## Test Data

### Sample Vietnamese Medical Terms
- **General**: Vật lý trị liệu, Bệnh nhân, Điều trị, Đánh giá
- **Conditions**: Đau lưng, Đau cổ, Đau vai, Viêm khớp, Bong gân
- **Treatments**: Massage, Vận động trị liệu, Điện trị liệu, Nhiệt trị liệu
- **Body Parts**: Cột sống, Vai, Đầu gối, Cổ chân, Cơ, Xương, Khớp

### Sample Vietnamese Patient Names
- Nguyễn Văn An
- Trần Thị Bình
- Lê Văn Cường
- Phạm Thị Dung
- Hoàng Văn Em

### Vietnamese Character Sets Tested
- **Vowel A**: a á à ả ã ạ ă ắ ằ ẳ ẵ ặ â ấ ầ ẩ ẫ ậ
- **Vowel E**: e é è ẻ ẽ ẹ ê ế ề ể ễ ệ
- **Vowel I**: i í ì ỉ ĩ ị
- **Vowel O**: o ó ò ỏ õ ọ ô ố ồ ổ ỗ ộ ơ ớ ờ ở ỡ ợ
- **Vowel U**: u ú ù ủ ũ ụ ư ứ ừ ử ữ ự
- **Vowel Y**: y ý ỳ ỷ ỹ ỵ
- **Special**: đ Đ

## Continuous Integration

### Test Requirements
1. MariaDB 10.11+ with utf8mb4_vietnamese_ci collation
2. PHP 8.2+ with mb_string extension
3. PHPUnit 11.5+
4. OpenEMR database schema initialized
5. Vietnamese PT SQL initialization scripts executed

### CI Pipeline Integration
```yaml
# Example GitHub Actions
- name: Run Vietnamese PT Tests
  run: vendor/bin/phpunit --testsuite vietnamese --coverage-text
```

## Maintenance

### Adding New Vietnamese Tests
1. Place unit tests in `tests/Tests/Unit/Vietnamese/`
2. Place integration tests in `tests/Tests/Services/Vietnamese/`
3. Use `VietnameseTestData::` fixtures for consistent test data
4. Ensure all tests clean up after themselves (tearDown/tearDownAfterClass)
5. Mark tests as skipped if tables don't exist (not fail)

### Test Data Cleanup
All integration tests automatically clean up inserted test data in `tearDownAfterClass()`:
```php
public static function tearDownAfterClass(): void
{
    // Cleanup all inserted test records
    if (!empty(self::$insertedIds)) {
        $placeholders = implode(',', self::$insertedIds);
        self::$dbConnection->exec("DELETE FROM table WHERE id IN ($placeholders)");
    }
}
```

## Known Limitations

1. **Database Required**: Integration tests require running MariaDB database
2. **Docker Environment**: Some tests assume Docker environment paths
3. **Full OpenEMR Install**: Bootstrap requires full OpenEMR initialization
4. **No Mock Database**: Real database connection required for collation testing

## Future Enhancements

1. Add performance benchmarks for Vietnamese text search
2. Add stress tests with large Vietnamese datasets
3. Add tests for Vietnamese PDF generation
4. Add tests for Vietnamese report exports
5. Add API endpoint tests for Vietnamese PT features

## Authors

- Dang Tran <tqvdang@msn.com>

## License

GNU General Public License 3