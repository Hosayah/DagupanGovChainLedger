<?php
class AuditTrailDao
{
    private $conn;

    public function __construct($dbConn)
    {
        $this->conn = $dbConn;
    }

    /**
     * Log a generic audit trail action
     */
    public function logAction($auditId, $action, $note, $userId): bool
    {
        $sql = "INSERT INTO audit_trail (audit_id, action, note, performed_by) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issi", $auditId, $action, $note, $userId);
        return $stmt->execute();
    }

    /**
     * Convenience method: mark audit as VIEWED
     */
    public function logViewed($auditId, $userId): bool
    {
        return $this->logAction($auditId, "VIEWED", null, $userId);
    }

    /**
     * Convenience method: COMMENTED on audit
     */
    public function logComment($auditId, $note, $userId): bool
    {
        return $this->logAction($auditId, "COMMENTED", $note, $userId);
    }

    /**
     * Convenience method: ESCALATED
     */
    public function logEscalated($auditId, $note, $userId): bool
    {
        return $this->logAction($auditId, "ESCALATED", $note, $userId);
    }

    /**
     * Convenience method: DISPUTED (agency disputes)
     */
    public function logDisputed($auditId, $note, $userId): bool
    {
        return $this->logAction($auditId, "DISPUTED", $note, $userId);
    }

    /**
     * Retrieve all actions for a specific audit
     */
    public function getTrailByAuditIdWithSearch($auditId, $offset = 0, $search_term = ''): mixed{
        $sql = "
        SELECT 
            at.trail_id,
            at.audit_id,
            at.action,
            at.note,
            at.performed_by,
            u.full_name,
            at.created_at
        FROM audit_trail at
        JOIN users u ON at.performed_by = u.user_id
        WHERE at.audit_id = ?
    ";

        $params = [$auditId];
        $types = "i";

        // ✅ Optional search on action or user name or note
        if (!empty($search_term)) {
            $sql .= " AND ( 
            at.action LIKE ? 
            OR u.full_name LIKE ?
            OR at.note LIKE ?
        )";
            $search_like = '%' . $search_term . '%';
            $params[] = $search_like;
            $params[] = $search_like;
            $params[] = $search_like;
            $types .= "sss";
        }

        // ✅ Order by latest first
        $sql .= " ORDER BY at.created_at DESC";

        // ✅ Pagination
        $sql .= " LIMIT 5 OFFSET ?";
        $params[] = $offset;
        $types .= "i";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result();
    }

}
