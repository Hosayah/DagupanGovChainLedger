<?php
class AuditDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // get all users
    public function getAllAudit($limit = 0): mixed {
        $sql = "
            SELECT * FROM audits LIMIT 5 OFFSET ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result; 
    }
    public function getAllAuditWithSearch($limit = 0, $search_term = ''): mixed
    {
        // Base SQL
        $sql = "SELECT * FROM audits WHERE 1=1";
        $params = [];
        $types = "";

        // ✅ Add search filter only if search term is provided
        if (!empty($search_term)) {
            $sql .= " AND (title LIKE ? OR result LIKE ? OR audit_by LIKE ?)";
            $search_like = '%' . $search_term . '%';
            $params[] = $search_like;
            $params[] = $search_like;
            $params[] = $search_like;
            $types .= "sss";
        }

        // ✅ Add pagination (limit & offset)
        $sql .= " LIMIT 5 OFFSET ?";
        $params[] = $limit;
        $types .= "i";

        // Prepare and bind
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        return $stmt->get_result();
    }


    public function getAuditByUserId($id, $limit = 0): mixed {
        $sql = "
            SELECT * FROM audits WHERE audit_by = ? ORDER BY audited_at DESC LIMIT 5 OFFSET ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $limit);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result; 
    }
    public function getAuditByUserIdWithSearch($user_id, $limit = 0, $search_term = ''): mixed{
        $sql = "
            SELECT 
                audit_id,
                title,
                record_id,
                result,
                audit_by,
                audited_at
            FROM audits
            WHERE audit_by = ?
        ";

        $params = [$user_id];
        $types = "i";

        // ✅ Add search capability
        if (!empty($search_term)) {
            $sql .= "
                AND (
                    title LIKE ? 
                    OR result LIKE ? 
                    OR record_id LIKE ?
                )
            ";
            $search_like = '%' . $search_term . '%';
            $params[] = $search_like;
            $params[] = $search_like;
            $params[] = $search_like;
            $types .= "sss";
        }

        // ✅ Add pagination
        $sql .= " LIMIT 5 OFFSET ?";
        $params[] = $limit;
        $types .= "i";

        // ✅ Prepare, bind, and execute
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->get_result();
    }

    public function getAuditById($id): mixed {
        $sql = "
            SELECT * FROM audits WHERE audit_id = ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result->fetch_assoc(); 
    }

    public function getAuditByRecordId($id): mixed {
        $sql = "
            SELECT * FROM audits WHERE record_id = ? ORDER BY audited_at DESC LIMIT 2;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result; 
    }

    public function addAudit($recordId, $title, $summary, $result, $document_hash, $document_cid, $tx_hash, $userId): mixed {
        if (is_numeric($result)) {
            $result = match((int)$result) {
                0 => 'PASSED',
                1 => 'FLAGGED',
                2 => 'REJECTED',
                default => 'UNKNOWN'
            };
        }
        $sql = "
            INSERT INTO audits (record_id, title, summary, result, document_hash, document_cid, tx_hash, audit_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?);
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssssi", $recordId, $title, $summary, $result, $document_hash, $document_cid, $tx_hash, $userId);
        return $stmt->execute();   
    }
     public function updateAudit($audit_id, $title, $summary): mixed {
        $sql = "
            UPDATE audits SET title = ?, summary = ? WHERE audit_id = ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $summary, $audit_id);
        return $stmt->execute();   
    }
   
    /**
     * Get counters: total users, approved, pending, agency, auditor, citizen
     */
    public function getAuditCounters($id) {
        $data = [
            "total" => 0,
            "auditorTotal" => 0,
            "passed" => 0,
            "flagged" => 0,
            "rejected"=> 0,
            "auditorPassed" => 0,
            "auditorFlagged"=> 0,
            "auditorRejected"=> 0,
        ];

        // Records projects
        $res = $this->conn->query("SELECT COUNT(audit_id) AS count FROM audits");
        if ($res) {
            $row = $res->fetch_assoc();
            $data["total"] = $row["count"];
        }

        // Records count per auditor
        $res = $this->conn->prepare("SELECT COUNT(audit_id) AS count FROM audits WHERE audit_by = ?");
        $res->bind_param("i", $id);
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["auditorTotal"] = $row["count"];
            }
        }

        // Records count for passed audits
        $res = $this->conn->prepare("SELECT COUNT(audit_id) AS count FROM audits WHERE result = 'PASSED';");
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["passed"] = $row["count"];
            }
        }
        // Records count for flagged audits
        $res = $this->conn->prepare("SELECT COUNT(audit_id) AS count FROM audits WHERE result = 'FLAGGED';");
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["flagged"] = $row["count"];
            }
        }


        // Records count for rejected audits
        $res = $this->conn->prepare("SELECT COUNT(audit_id) AS count FROM audits WHERE result = 'REJECTED';");
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["rejected"] = $row["count"];
            }
        }

        
        // Records count for passed audits of an auditor
        $res = $this->conn->prepare("SELECT COUNT(audit_id) AS count FROM audits WHERE result = 'PASSED' AND audit_by = ?");
        $res->bind_param("i", $id);
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["auditorPassed"] = $row["count"];
            }
        }

        // Records count for flagged audits of an auditor
        $res = $this->conn->prepare("SELECT COUNT(audit_id) AS count FROM audits WHERE result = 'FLAGGED' AND audit_by = ?");
        $res->bind_param("i", $id);
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["auditorFlagged"] = $row["count"];
            }
        }
        

        // Records count for rejected audits of an auditor
        $res = $this->conn->prepare("SELECT COUNT(audit_id) AS count FROM audits WHERE result = 'REJECTED' AND audit_by = ?");
        $res->bind_param("i", $id);
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["auditorRejected"] = $row["count"];
            }
        }

        
        return $data;
    }
    public function getAuditCountersByRecordId($id) {
        $data = [
            "total" => 0,
            "passed" => 0,
            "flagged" => 0,
            "rejected"=> 0,
        ];

        // Records projects
        $res = $this->conn->prepare("SELECT COUNT(audit_id) AS count FROM audits WHERE record_id = ?;");
        $res->bind_param("i", $id);
        if ($res->execute()) {
           $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["total"] = $row["count"];
            }
        }


        // Records count for passed audits
        $res = $this->conn->prepare("SELECT COUNT(audit_id) AS count FROM audits WHERE result = 'PASSED' AND record_id = ?;");
        $res->bind_param("i", $id);
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["passed"] = $row["count"];
            }
        }
        // Records count for flagged audits
        $res = $this->conn->prepare("SELECT COUNT(audit_id) AS count FROM audits WHERE result = 'FLAGGED' AND record_id = ?;");
        $res->bind_param("i", $id);
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["flagged"] = $row["count"];
            }
        }


        // Records count for rejected audits
        $res = $this->conn->prepare("SELECT COUNT(audit_id) AS count FROM audits WHERE result = 'REJECTED' AND record_id = ?;");
        $res->bind_param("i", $id);
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["rejected"] = $row["count"];
            }
        }
        
        return $data;
    }
}
