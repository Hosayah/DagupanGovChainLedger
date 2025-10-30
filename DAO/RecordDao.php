<?php
class RecordDAO
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // get all users
    public function getAllRecords($limit = 0): mixed
    {
        $sql = "
            SELECT * FROM records LIMIT 5 OFFSET ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result;
    }
    public function getAllRecordsWithSearch($limit = 0, $search_term = ''): mixed
    {
        // Base SQL with JOIN between records and projects
        $sql = "
            SELECT 
                r.record_id,
                r.project_id,
                r.record_type,
                r.amount,
                r.document_hash,
                r.document_cid,
                r.blockchain_tx,
                r.submitted_by,
                r.submitted_at,
                p.title AS project_title,
                p.category AS project_category
            FROM records r
            INNER JOIN projects p ON r.project_id = p.project_id
            WHERE 1=1
        ";

        $params = [];
        $types = "";

        // ✅ Optional search filter
        if (!empty($search_term)) {
            $sql .= " 
                AND (
                    p.title LIKE ? 
                    OR p.category LIKE ? 
                    OR r.record_type LIKE ?
                    OR r.document_hash LIKE ?
                )
            ";
            $search_like = '%' . $search_term . '%';
            $params = [$search_like, $search_like, $search_like, $search_like];
            $types = "ssss";
        }

        // ✅ Pagination
        $sql .= " LIMIT 5 OFFSET ?";
        $params[] = $limit;
        $types .= "i";

        // ✅ Prepare & bind
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result();
    }
    public function getProjectByUserIdWithSearch($user_id, $limit = 0, $search_term = ''): mixed
    {
        $sql = "
            SELECT 
                r.record_id,
                r.project_id,
                r.record_type,
                r.amount,
                r.document_hash,
                r.document_cid,
                r.blockchain_tx,
                r.submitted_by,
                r.submitted_at,
                p.title AS project_title,
                p.category AS project_category
            FROM records r
            INNER JOIN projects p ON r.project_id = p.project_id
            WHERE r.submitted_by = ?
        ";

        $params = [$user_id];
        $types = "i";

        // ✅ Add optional search filter
        if (!empty($search_term)) {
            $sql .= " 
                AND (
                    p.title LIKE ? 
                    OR p.category LIKE ? 
                    OR r.record_type LIKE ?
                    OR r.document_hash LIKE ?
                )
            ";
            $search_like = '%' . $search_term . '%';
            $params[] = $search_like;
            $params[] = $search_like;
            $params[] = $search_like;
            $params[] = $search_like;
            $types .= "ssss";
        }

        // ✅ Pagination
        $sql .= " LIMIT 5 OFFSET ?";
        $params[] = $limit;
        $types .= "i";

        // ✅ Prepare and bind
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getProjectByUserId($id, $limit = 0): mixed
    {
        $sql = "
            SELECT * FROM records WHERE submitted_by = ? LIMIT 5 OFFSET ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $limit);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result;
    }

    /**
     * Get latest projects (default 5)
     */
    public function getRecordByProjectId($id, $limit = 0): mixed
    {
        $sql = "
            SELECT * FROM records WHERE project_id = ? LIMIT 5 OFFSET ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $limit);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result->fetch_assoc();
    }

    public function addRecord($id, $record_type, $amount, $document_hash, $blockchain_tx): void
    {
        $sql = "
           INSERT INTO records (project_id, record_type, amount, document_hash, blockchain_tx)
              VALUES (?, ?, ?, ?)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $id, );
        $stmt->execute();
        //return $stmt->get_result();
    }
    /**
     * Get counters: total users, approved, pending, agency, auditor, citizen
     */
    public function getRecordCounters($id)
    {
        $data = [
            "total" => 0,
            "orgTotal" => 0,
            "sum" => 0,
            "orgSum" => 0,
        ];

        // --- Total Records (all)
        $res = $this->conn->query("SELECT COUNT(record_id) AS count FROM records");
        if ($res) {
            $row = $res->fetch_assoc();
            $data["total"] = (int) $row["count"];
        }

        // --- Records count per agency
        $res = $this->conn->prepare("SELECT COUNT(record_id) AS count FROM records WHERE submitted_by = ?");
        $res->bind_param("i", $id);
        if ($res->execute()) {
            $result = $res->get_result();
            if ($row = $result->fetch_assoc()) {
                $data["orgTotal"] = (int) $row["count"];
            }
        }

        // --- Sum of records that are PASSED only
        $sqlSum = "
        SELECT SUM(r.amount) AS sum
        FROM records r
        INNER JOIN (
            SELECT a1.record_id, a1.result
            FROM audits a1
            WHERE a1.audited_at = (
                SELECT MAX(a2.audited_at)
                FROM audits a2
                WHERE a2.record_id = a1.record_id
            )
        ) latest_audit ON latest_audit.record_id = r.record_id
        WHERE latest_audit.result = 'PASSED'
    ";
        $res = $this->conn->query($sqlSum);
        if ($res) {
            $row = $res->fetch_assoc();
            $data["sum"] = (float) ($row["sum"] ?? 0);
        }

        // --- Sum of PASSED records per agency
        $sqlOrgSum = "
        SELECT SUM(r.amount) AS sum
        FROM records r
        INNER JOIN (
            SELECT a1.record_id, a1.result
            FROM audits a1
            WHERE a1.audited_at = (
                SELECT MAX(a2.audited_at)
                FROM audits a2
                WHERE a2.record_id = a1.record_id
            )
        ) latest_audit ON latest_audit.record_id = r.record_id
        WHERE latest_audit.result = 'PASSED' AND r.submitted_by = ?
    ";
        $res = $this->conn->prepare($sqlOrgSum);
        $res->bind_param("i", $id);
        if ($res->execute()) {
            $result = $res->get_result();
            if ($row = $result->fetch_assoc()) {
                $data["orgSum"] = (float) ($row["sum"] ?? 0);
            }
        }

        return $data;
    }

    public function getSumPerCategory()
    {
        $sql = "
        SELECT 
            p.category,
            COALESCE(SUM(r.amount), 0) AS total_amount
        FROM 
            projects p
        LEFT JOIN 
            records r ON r.project_id = p.project_id
        INNER JOIN (
            SELECT a1.record_id, a1.result
            FROM audits a1
            WHERE a1.audited_at = (
                SELECT MAX(a2.audited_at)
                FROM audits a2
                WHERE a2.record_id = a1.record_id
            )
        ) latest_audit ON latest_audit.record_id = r.record_id
        WHERE latest_audit.result = 'PASSED'
        GROUP BY 
            p.category
    ";

        $result = $this->conn->query($sql);
        $categoryTotals = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categoryTotals[$row['category']] = (float) $row['total_amount'];
            }
        }

        return $categoryTotals;
    }

}
