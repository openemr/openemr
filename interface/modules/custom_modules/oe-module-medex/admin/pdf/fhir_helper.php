<?php
/**
 * FHIR Execution Helper for MedEx PDF System
 */

class MedExFullFHIRExecutor {
    
    public static function executePath($pid, $encounter, $path) {
        // Simple internal implementation for now that maps common FHIR paths to SQL
        // In a real scenario, this would call the OpenEMR FHIR API internally
        
        $parts = explode('.', $path);
        $resource = $parts[0];
        
        if ($resource === 'Patient') {
            $sql = "SELECT * FROM patient_data WHERE pid = ?";
            $row = sqlQuery($sql, [$pid]);
            if (!$row) return '';
            
            // Map common paths
            if (strpos($path, 'name.given[0]') !== false) return $row['fname'];
            if (strpos($path, 'name.family') !== false) return $row['lname'];
            if (strpos($path, 'birthDate') !== false) return $row['DOB'];
            if (strpos($path, 'gender') !== false) return $row['sex'];
            if (strpos($path, 'telecom[phone]') !== false) return $row['phone_cell'];
            if (strpos($path, 'telecom[email]') !== false) return $row['email'];
            if (strpos($path, 'address[0].line[0]') !== false) return $row['street'];
            if (strpos($path, 'address[0].city') !== false) return $row['city'];
            if (strpos($path, 'address[0].state') !== false) return $row['state'];
            if (strpos($path, 'address[0].postalCode') !== false) return $row['postal_code'];
            if (strpos($path, 'identifier[SSN]') !== false) return $row['ss'];
            if (strpos($path, 'identifier[MR]') !== false) return $row['pid'];
        }
        
        if ($resource === 'Practitioner') {
             // We need to find the provider for the patient or encounter
             $provId = 0;
             if ($encounter) {
                 $enc = sqlQuery("SELECT provider_id FROM form_encounter WHERE id = ?", [$encounter]);
                 if ($enc) $provId = $enc['provider_id'];
             }
             if (!$provId) {
                 $pat = sqlQuery("SELECT providerID FROM patient_data WHERE pid = ?", [$pid]);
                 if ($pat) $provId = $pat['providerID'];
             }
             
             if ($provId) {
                 $row = sqlQuery("SELECT * FROM users WHERE id = ?", [$provId]);
                 if ($row) {
                     if (strpos($path, 'name.given[0]') !== false) return $row['fname'];
                     if (strpos($path, 'name.family') !== false) return $row['lname'];
                     if (strpos($path, 'identifier[NPI]') !== false) return $row['npi'] ?? $row['npin'];
                 }
             }
        }
        
        // TODO: Use real FHIR internal call via Guzzle/Curl to localhost:8300/apis/default/fhir if needed for complex paths
        
        return ''; 
    }
}
