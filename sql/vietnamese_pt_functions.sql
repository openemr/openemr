-- Vietnamese PT Medical Term Translation Functions
--
-- @package   OpenEMR
-- @link      http://www.open-emr.org
-- @author    Dang Tran <tqvdang@msn.com>
-- @copyright Copyright (c) 2025 Dang Tran
-- @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
--
-- This file creates stored functions for translating medical terms
-- between English and Vietnamese using the vietnamese_medical_terms table.
--
-- Usage:
--   SELECT get_vietnamese_term('pain');
--   SELECT get_english_term('đau');

-- Drop existing functions if they exist
DROP FUNCTION IF EXISTS get_vietnamese_term;
DROP FUNCTION IF EXISTS get_english_term;

DELIMITER //

-- Function: get_vietnamese_term
-- Purpose: Translate an English medical term to Vietnamese
-- Parameters: term (VARCHAR) - English medical term to translate
-- Returns: VARCHAR - Vietnamese translation, or original term if not found
CREATE FUNCTION get_vietnamese_term(term VARCHAR(255))
RETURNS VARCHAR(255)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE viet_term VARCHAR(255);

    -- Lookup the Vietnamese term from the medical terms table
    -- Using explicit COLLATE to avoid collation mismatch errors
    SELECT vietnamese_term INTO viet_term
    FROM vietnamese_medical_terms
    WHERE english_term COLLATE utf8mb4_general_ci = term COLLATE utf8mb4_general_ci
    LIMIT 1;

    -- Return the Vietnamese term if found, otherwise return the original term
    RETURN COALESCE(viet_term, term);
END //

-- Function: get_english_term
-- Purpose: Translate a Vietnamese medical term to English
-- Parameters: term (VARCHAR) - Vietnamese medical term to translate
-- Returns: VARCHAR - English translation, or original term if not found
CREATE FUNCTION get_english_term(term VARCHAR(255))
RETURNS VARCHAR(255)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE eng_term VARCHAR(255);

    -- Lookup the English term from the medical terms table
    -- Using explicit COLLATE to avoid collation mismatch errors
    SELECT english_term INTO eng_term
    FROM vietnamese_medical_terms
    WHERE vietnamese_term COLLATE utf8mb4_vietnamese_ci = term COLLATE utf8mb4_vietnamese_ci
    LIMIT 1;

    -- Return the English term if found, otherwise return the original term
    RETURN COALESCE(eng_term, term);
END //

DELIMITER ;

-- Verify functions were created successfully
SELECT 'Vietnamese PT translation functions created successfully' AS status;

-- Example usage tests (uncomment to test after medical terms are populated):
-- SELECT get_vietnamese_term('pain') AS pain_vietnamese;
-- SELECT get_english_term('đau') AS pain_english;
-- SELECT get_vietnamese_term('physical therapy') AS pt_vietnamese;
-- SELECT get_english_term('vật lý trị liệu') AS pt_english;
