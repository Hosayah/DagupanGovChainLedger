<?php
class RecordDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // get all users
    public function getAllRecords($limit = 0): mixed {
        $sql = "
            SELECT * FROM records LIMIT 5 OFFSET ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result; 
    }

    public function getProjectByUserId($id, $limit = 0): mixed {
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
    public function getRecordByProjectId($id, $limit = 0): mixed {
        $sql = "
            SELECT * FROM records WHERE project_id = ? LIMIT 5 OFFSET ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $limit);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result; 
    }

    public function addProjectById($id): void {
        $sql = "
           INSERT INTO records (project_id, record_type, amount, document_path, document_hash)
           VALUES (?, ?, ?, ?, ?)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id,);
        $stmt->execute();
        return $stmt->get_result();
    }
    /**
     * Get counters: total users, approved, pending, agency, auditor, citizen
     */
    public function getRecordCounters($id) {
        $data = [
            "total" => 0,
            "orgTotal" => 0,
            "sum" => 0,
            "orgSum" => 0,

        ];

        // Records projects
        $res = $this->conn->query("SELECT COUNT(record_id) AS count FROM records");
        if ($res) {
            $row = $res->fetch_assoc();
            $data["total"] = $row["count"];
        }

        // Records count per Agency
        $res = $this->conn->prepare("SELECT COUNT(record_id) AS count FROM records WHERE submitted_by = ?");
        $res->bind_param("i", $id);
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["orgTotal"] = $row["count"];
            }
        }

        // Sum of all amounts
        $res = $this->conn->query("SELECT SUM(amount) AS sum FROM records");
        if ($res) {
            $row = $res->fetch_assoc();
            $data["sum"] = $row["sum"];
        }
        
        $res = $this->conn->prepare("SELECT SUM(amount) AS sum FROM records WHERE submitted_by = ?");
        $res->bind_param("i", $id);
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["orgSum"] = $row["sum"];
            }
        }

        
        return $data;
    }
}
