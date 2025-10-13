<?php

/**
 * ProcedureOrderRelationshipService.php
 *
 * Service for managing relationships between procedure orders and related clinical resources
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;

/**
 * Manages the procedure_order_relationships junction table
 * Links ServiceRequests to related Observations, Conditions, DocumentReferences, etc.
 */
class ProcedureOrderRelationshipService
{
    /**
     * Add a relationship link to a procedure order
     *
     * @param int    $procedureOrderId The procedure_order.procedure_order_id
     * @param string $resourceType     FHIR resource type (e.g., 'Observation', 'Condition')
     * @param string $resourceUuid     UUID string of the related resource
     * @param string $relationship     Optional relationship type (e.g., 'supporting-info', 'reason-reference')
     * @param int    $userId           User creating the link
     * @return int|false The inserted ID or false on failure
     */
    public function addRelationship($procedureOrderId, $resourceType, $resourceUuid, $relationship = null, $userId = null)
    {
        // Validate procedure order exists (application-level referential integrity)
        if (!$this->procedureOrderExists($procedureOrderId)) {
            return false;
        }

        // Convert UUID string to binary
        $uuidBinary = UuidRegistry::uuidToBytes($resourceUuid);
        if ($uuidBinary === false) {
            return false;
        }

        $sql = "INSERT INTO procedure_order_relationships 
                (procedure_order_id, resource_type, resource_uuid, relationship, created_by)
                VALUES (?, ?, ?, ?, ?)";

        $result = sqlInsert($sql, [
            $procedureOrderId,
            $resourceType,
            $uuidBinary,
            $relationship,
            $userId
        ]);

        return $result;
    }

    /**
     * Get all relationships for a procedure order
     *
     * @param int $procedureOrderId The procedure_order.procedure_order_id
     * @return array Array of relationship records with UUIDs as strings
     */
    public function getRelationshipsByOrderId($procedureOrderId)
    {
        $sql = "SELECT id, procedure_order_id, resource_type, resource_uuid, 
                       relationship, created_at, created_by
                FROM procedure_order_relationships
                WHERE procedure_order_id = ?
                ORDER BY created_at";

        $result = sqlStatement($sql, [$procedureOrderId]);

        $relationships = [];
        while ($row = sqlFetchArray($result)) {
            // Convert UUID binary to string
            $row['resource_uuid'] = UuidRegistry::uuidToString($row['resource_uuid']);
            $relationships[] = $row;
        }

        return $relationships;
    }

    /**
     * Get relationships filtered by resource type
     *
     * @param int    $procedureOrderId
     * @param string $resourceType (e.g., 'Observation', 'Condition')
     * @return array
     */
    public function getRelationshipsByType($procedureOrderId, $resourceType)
    {
        $sql = "SELECT id, procedure_order_id, resource_type, resource_uuid, 
                       relationship, created_at, created_by
                FROM procedure_order_relationships
                WHERE procedure_order_id = ? AND resource_type = ?
                ORDER BY created_at";

        $result = sqlStatement($sql, [$procedureOrderId, $resourceType]);

        $relationships = [];
        while ($row = sqlFetchArray($result)) {
            $row['resource_uuid'] = UuidRegistry::uuidToString($row['resource_uuid']);
            $relationships[] = $row;
        }

        return $relationships;
    }

    /**
     * Remove a relationship link
     *
     * @param int $id The procedure_order_relationships.id
     * @return bool Success status
     */
    public function deleteRelationship($id)
    {
        $sql = "DELETE FROM procedure_order_relationships WHERE id = ?";
        return sqlStatement($sql, [$id]) !== false;
    }

    /**
     * Remove all relationships for a procedure order
     * Called when deleting a procedure order
     *
     * @param int $procedureOrderId The procedure_order.procedure_order_id
     * @return bool Success status
     */
    public function deleteRelationshipsByOrderId($procedureOrderId)
    {
        $sql = "DELETE FROM procedure_order_relationships WHERE procedure_order_id = ?";
        return sqlStatement($sql, [$procedureOrderId]) !== false;
    }

    /**
     * Remove relationships by resource type
     *
     * @param int    $procedureOrderId
     * @param string $resourceType
     * @return bool Success status
     */
    public function deleteRelationshipsByType($procedureOrderId, $resourceType)
    {
        $sql = "DELETE FROM procedure_order_relationships 
                WHERE procedure_order_id = ? AND resource_type = ?";
        return sqlStatement($sql, [$procedureOrderId, $resourceType]) !== false;
    }

    /**
     * Check if a specific relationship link exists
     *
     * @param int    $procedureOrderId
     * @param string $resourceType
     * @param string $resourceUuid
     * @return bool
     */
    public function relationshipExists($procedureOrderId, $resourceType, $resourceUuid)
    {
        $uuidBinary = UuidRegistry::uuidToBytes($resourceUuid);
        if ($uuidBinary === false) {
            return false;
        }

        $sql = "SELECT COUNT(*) as count 
                FROM procedure_order_relationships
                WHERE procedure_order_id = ? 
                  AND resource_type = ? 
                  AND resource_uuid = ?";

        $result = sqlQuery($sql, [$procedureOrderId, $resourceType, $uuidBinary]);
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Validate that procedure order exists
     * Application-level referential integrity check
     *
     * @param int $procedureOrderId
     * @return bool
     */
    private function procedureOrderExists($procedureOrderId)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM procedure_order 
                WHERE procedure_order_id = ? AND activity = 1";

        $result = sqlQuery($sql, [$procedureOrderId]);
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Get relationships formatted for FHIR ServiceRequest.supportingInfo
     *
     * @param int $procedureOrderId
     * @return array Array of ['resource_type' => string, 'uuid' => string, 'relationship' => string]
     */
    public function getRelationshipsForFhir($procedureOrderId)
    {
        $relationships = $this->getRelationshipsByOrderId($procedureOrderId);

        $fhirFormat = [];
        foreach ($relationships as $rel) {
            $fhirFormat[] = [
                'resource_type' => $rel['resource_type'],
                'uuid' => $rel['resource_uuid'],
                'relationship' => $rel['relationship']
            ];
        }

        return $fhirFormat;
    }

    /**
     * Batch sync relationships from FHIR resource
     * Used when creating/updating ServiceRequest via FHIR API
     * Replaces all existing relationships with new ones
     *
     * @param int   $procedureOrderId
     * @param array $relationshipsArray Array of ['resource_type' => string, 'uuid' => string, 'relationship' => string]
     * @param int   $userId
     * @return bool Success status
     */
    public function syncRelationshipsFromFhir($procedureOrderId, $relationshipsArray, $userId = null)
    {
        // First, remove all existing relationships
        $this->deleteRelationshipsByOrderId($procedureOrderId);

        // Then add new ones
        if (empty($relationshipsArray)) {
            return true;
        }

        $success = true;
        foreach ($relationshipsArray as $rel) {
            if (!isset($rel['resource_type']) || !isset($rel['uuid'])) {
                continue;
            }

            $result = $this->addRelationship(
                $procedureOrderId,
                $rel['resource_type'],
                $rel['uuid'],
                $rel['relationship'] ?? 'supporting-info',
                $userId
            );

            if ($result === false) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Clean up orphaned relationship records
     * Application-level CASCADE DELETE equivalent
     * Should be called periodically or after bulk deletions
     *
     * @return int Number of records deleted
     */
    public function cleanupOrphanedRecords(): int
    {
        $sql = "DELETE por FROM procedure_order_relationships por
                LEFT JOIN procedure_order po ON por.procedure_order_id = po.procedure_order_id
                WHERE po.procedure_order_id IS NULL OR po.activity = 0";

        return sqlStatement($sql);
    }

    /**
     * Get statistics about relationship usage
     * Useful for monitoring and optimization
     *
     * @return array Statistics
     */
    public function getStatistics()
    {
        $stats = [];

        // Total relationships
        $sql = "SELECT COUNT(*) as total FROM procedure_order_relationships";
        $result = sqlQuery($sql);
        $stats['total_relationships'] = $result['total'] ?? 0;

        // By resource type
        $sql = "SELECT resource_type, COUNT(*) as count 
                FROM procedure_order_relationships
                GROUP BY resource_type
                ORDER BY count DESC";
        $result = sqlStatement($sql);
        $stats['by_resource_type'] = [];
        while ($row = sqlFetchArray($result)) {
            $stats['by_resource_type'][$row['resource_type']] = $row['count'];
        }

        // Orders with relationships
        $sql = "SELECT COUNT(DISTINCT procedure_order_id) as count 
                FROM procedure_order_relationships";
        $result = sqlQuery($sql);
        $stats['orders_with_relationships'] = $result['count'] ?? 0;

        // By relationship type
        $sql = "SELECT relationship, COUNT(*) as count 
                FROM procedure_order_relationships
                WHERE relationship IS NOT NULL
                GROUP BY relationship
                ORDER BY count DESC";
        $result = sqlStatement($sql);
        $stats['by_relationship_type'] = [];
        while ($row = sqlFetchArray($result)) {
            $stats['by_relationship_type'][$row['relationship']] = $row['count'];
        }

        // Orphaned records
        $sql = "SELECT COUNT(*) as count 
                FROM procedure_order_relationships por
                LEFT JOIN procedure_order po ON por.procedure_order_id = po.procedure_order_id
                WHERE po.procedure_order_id IS NULL OR po.activity = 0";
        $result = sqlQuery($sql);
        $stats['orphaned_records'] = $result['count'] ?? 0;

        return $stats;
    }

    /**
     * Get all orders that reference a specific resource
     * Useful for finding which orders are affected by changes to a resource
     *
     * @param string $resourceType FHIR resource type
     * @param string $resourceUuid UUID of the resource
     * @return array Array of procedure_order_id values
     */
    public function getOrdersReferencingResource($resourceType, $resourceUuid)
    {
        $uuidBinary = UuidRegistry::uuidToBytes($resourceUuid);
        if ($uuidBinary === false) {
            return [];
        }

        $sql = "SELECT DISTINCT procedure_order_id
                FROM procedure_order_relationships
                WHERE resource_type = ? AND resource_uuid = ?";

        $result = sqlStatement($sql, [$resourceType, $uuidBinary]);

        $orderIds = [];
        while ($row = sqlFetchArray($result)) {
            $orderIds[] = $row['procedure_order_id'];
        }

        return $orderIds;
    }

    /**
     * Batch add relationships
     * More efficient than calling addRelationship multiple times
     *
     * @param int   $procedureOrderId
     * @param array $relationshipsArray Array of ['resource_type', 'uuid', 'relationship']
     * @param int   $userId
     * @return int Number of relationships added
     */
    public function batchAddRelationships($procedureOrderId, $relationshipsArray, $userId = null)
    {
        if (empty($relationshipsArray)) {
            return 0;
        }

        // Validate procedure order exists
        if (!$this->procedureOrderExists($procedureOrderId)) {
            return 0;
        }

        $added = 0;
        foreach ($relationshipsArray as $rel) {
            if (!isset($rel['resource_type']) || !isset($rel['uuid'])) {
                continue;
            }

            $result = $this->addRelationship(
                $procedureOrderId,
                $rel['resource_type'],
                $rel['uuid'],
                $rel['relationship'] ?? null,
                $userId
            );

            if ($result !== false) {
                $added++;
            }
        }

        return $added;
    }
}
