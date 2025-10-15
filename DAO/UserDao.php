<?php
class UserDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // get all users
    public function getAllUsers(): mixed {
        $sql = "
            SELECT * FROM users;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->get_result;
    }

    public function getUserById($id): mixed {
        $sql = "
            SELECT * FROM users WHERE user_id = ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result->fetch_assoc(); 
    }

    public function deleteUserById($id): void {
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    public function getUserByIdFromAgency($id): mixed {
        $sql = "
            SELECT * FROM agencies WHERE user_id = ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result->fetch_assoc(); 
    }
    public function getUserByIdFromAuditor($id): mixed {
        $sql = "
            SELECT * FROM auditors WHERE user_id = ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result->fetch_assoc(); 
    }
    /**
     * Get latest pending users (default 5)
     */
    public function getUsersByStatus($status, $limit = 5, $order_by = 'u.created_at', $order_dir = 'DESC') {
        $sql = "
            SELECT 
                u.user_id, 
                u.full_name, 
                u.email, 
                u.account_type,
                u.contact_number,
                u.status,
                u.created_at,
                COALESCE(a.agency_name, au.organization_name) AS organization,
                COALESCE(a.office_code, au.office_code) AS officeCode,
                COALESCE(a.gov_id_number, au.accreditation_number) AS identifier
            FROM users u
            LEFT JOIN agencies a ON u.user_id = a.user_id
            LEFT JOIN auditors au ON u.user_id = au.user_id
            WHERE u.status = ?
            ORDER BY $order_by $order_dir
            LIMIT 5 OFFSET ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status ,$limit);
        $stmt->execute();
        return $stmt->get_result();
    }
    public function getUsersByRole($type, $limit = 0) {
        $sql = "
            SELECT 
                u.user_id, 
                u.full_name, 
                u.email, 
                u.account_type, 
                u.contact_number,
                u.status,
                u.created_at,
                COALESCE(ad.access_level) AS organization,
                COALESCE(a.agency_name, au.organization_name) AS organization,
                COALESCE(a.office_code, au.office_code) AS officeCode,
                COALESCE(a.gov_id_number, au.accreditation_number) AS identifier
            FROM users u
            LEFT JOIN agencies a ON u.user_id = a.user_id
            LEFT JOIN auditors au ON u.user_id = au.user_id
            LEFT JOIN admins ad ON u.user_id = ad.user_id
            WHERE u.account_type = ?
            ORDER BY u.user_id ASC
            LIMIT 5 OFFSET ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $type ,$limit);
        $stmt->execute();
        return $stmt->get_result();
    }
    /**
     * Get counters: total users, approved, pending, agency, auditor, citizen
     */
    public function getUserCounters() {
        $data = [
            "total" => 0,
            "approved" => 0,
            "pending" => 0,
            "agency" => 0,
            "auditor" => 0,
            "citizen" => 0
        ];

        // Total users
        $res = $this->conn->query("SELECT COUNT(user_id) AS count FROM users");
        if ($res) {
            $row = $res->fetch_assoc();
            $data["total"] = $row["count"];
        }

        // Approved vs Pending
        $res = $this->conn->query("SELECT COUNT(user_id) AS count, status FROM users GROUP BY status");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $status = strtolower($row["status"]);
                $data[$status] = $row["count"];
            }
        }

        // Account types
        $res = $this->conn->query("SELECT COUNT(user_id) AS count, account_type FROM users GROUP BY account_type");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $type = strtolower($row["account_type"]);
                $data[$type] = $row["count"];
            }
        }

        return $data;
    }
}
