<?php
class UserDAO
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // get all users
    public function getAllUsers(): mixed
    {
        $sql = "
            SELECT * FROM users;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->get_result;
    }
    public function getAllProjectsWithSearch($limit = 0, $search_term = '')
    {
        $sql = "
            SELECT p.project_id, p.title, p.category, p.description, p.created_by, p.created_at
            FROM projects p
            WHERE 1=1
        ";

        $params = [];

        // ✅ Add search filter if provided
        if (!empty($search_term)) {
            $sql .= " AND (p.title LIKE ? OR p.category LIKE ? OR p.description LIKE ?)";
            $search_like = "%" . $search_term . "%";
            $params[] = $search_like;
            $params[] = $search_like;
            $params[] = $search_like;
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT ?, 10"; // adjust per-page limit if needed
        $params[] = $limit;

        $stmt = $this->conn->prepare($sql);

        // ✅ Bind parameters dynamically
        $types = str_repeat('s', count($params) - 1) . 'i'; // all strings except last int for LIMIT
        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        return $stmt->get_result();
    }


    public function getUserById($id): mixed
    {
        $sql = "
            SELECT * FROM users WHERE user_id = ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result->fetch_assoc();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = $result->fetch_assoc() ?: null;
        $stmt->close();
        return $user;
    }

    public function getAdminAccessLevel(int $userId): ?string
    {
        $stmt = $this->conn->prepare("SELECT access_level FROM admins WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['access_level'] ?? null;
    }

    public function getWalletAddress(int $userId, string $accountType): ?string
    {
        $tableName = ($accountType === 'agency') ? 'agencies' : 'auditors';
        $stmt = $this->conn->prepare("SELECT wallet_address FROM $tableName WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['wallet_address'] ?? null;
    }
    public function insertUser($data)
    {
        $sql = "INSERT INTO users (account_type, email, password_hash, full_name, contact_number, status)
                VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "sssss",
            $data['account_type'],
            $data['email'],
            $data['password_hash'],
            $data['full_name'],
            $data['contact_number']
        );

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function insertAgency($userId, $data)
    {
        $sql = "INSERT INTO agencies (user_id, agency_name, office_code, position, gov_id_number, wallet_address)
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssss", $userId, $data['agency_name'], $data['office_code'], $data['position'], $data['gov_id_number'], $data['wallet_address']);
        return $stmt->execute();
    }

    public function insertAuditor($userId, $data)
    {
        $sql = "INSERT INTO auditors (user_id, organization_name, office_code, accreditation_number, wallet_address)
                    VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issss", $userId, $data['organization_name'], $data['office_code'], $data['accreditation_number'], $data['wallet_address']);
        return $stmt->execute();
    }
    public function getUserByIdFromAgency($id): mixed
    {
        $sql = "
            SELECT * FROM agencies WHERE user_id = ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result->fetch_assoc();
    }
    public function getUserByIdFromAuditor($id): mixed
    {
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
    public function getUsersByStatus($status, $limit = 5, $order_by = 'u.created_at', $order_dir = 'DESC')
    {
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
        $stmt->bind_param("si", $status, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
    public function getUsersByStatusWithSearch(
        $status,
        $limit = 0,
        $offset = 0,
        $search_term = '',
        $order_by = 'u.created_at',
        $order_dir = 'DESC'
    ): mixed {

        // ✅ Whitelist allowed columns for order_by to prevent SQL injection
        $allowed_order_columns = ['u.created_at', 'u.full_name', 'u.email', 'u.status'];
        if (!in_array($order_by, $allowed_order_columns)) {
            $order_by = 'u.created_at';
        }

        // ✅ Ensure order_dir is only ASC or DESC
        $order_dir = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';

        // ✅ Base query with JOINs (added admins table)
        $sql = "
        SELECT 
            u.user_id, 
            u.full_name, 
            u.email, 
            u.account_type,
            u.contact_number,
            u.status,
            u.created_at,
            ad.access_level,
            COALESCE(a.agency_name, au.organization_name) AS organization,
            COALESCE(a.office_code, au.office_code) AS officeCode,
            COALESCE(a.gov_id_number, au.accreditation_number) AS identifier
        FROM users u
        LEFT JOIN agencies a ON u.user_id = a.user_id
        LEFT JOIN auditors au ON u.user_id = au.user_id
        LEFT JOIN admins ad ON u.user_id = ad.user_id
        WHERE u.status = ?
        AND (ad.access_level IS NULL OR ad.access_level != 'super_admin')
    ";

        $params = [$status];
        $types = "s";

        // ✅ Add search filter if provided
        if (!empty($search_term)) {
            $sql .= "
            AND (
                u.full_name LIKE ? OR
                u.email LIKE ? OR
                u.account_type LIKE ? OR
                a.agency_name LIKE ? OR
                au.organization_name LIKE ?
            )
        ";
            $search_like = '%' . $search_term . '%';
            $params[] = $search_like;
            $params[] = $search_like;
            $params[] = $search_like;
            $params[] = $search_like;
            $params[] = $search_like;
            $types .= "sssss";
        }

        // ✅ Limit + Offset for pagination
        $sql .= " ORDER BY $order_by $order_dir LIMIT 5 OFFSET ?";
        $params[] = $offset;
        $types .= "i";

        // ✅ Prepare and bind params
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result();
    }


    public function updateUserById($name, $email, $contact, $id): mixed
    {
        $sql = "
            UPDATE users SET full_name = ?, email = ?, contact_number = ? WHERE user_id = ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $contact, $id);
        return $stmt->execute();
    }
    public function updatePasswordById($password, $id): mixed
    {
        $sql = "
            UPDATE users SET password_hash = ? WHERE user_id = ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $password, $id);
        return $stmt->execute();
    }
    public function getUsersByRole($type, $limit = 0)
    {
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
        $stmt->bind_param("si", $type, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
    public function getUsersByRoleWithSearch(
        $type,
        $limit = 5,
        $offset = 0,
        $search_term = '',
        $order_by = 'u.user_id',
        $order_dir = 'ASC'
    ): mixed {
        // ✅ Whitelist allowed columns to avoid SQL injection
        $allowed_order_columns = [
            'u.user_id',
            'u.full_name',
            'u.email',
            'u.account_type',
            'u.status',
            'u.created_at'
        ];
        if (!in_array($order_by, $allowed_order_columns)) {
            $order_by = 'u.user_id';
        }

        // ✅ Ensure order direction is valid
        $order_dir = strtoupper($order_dir) === 'DESC' ? 'DESC' : 'ASC';

        // ✅ Base query
        $sql = "
            SELECT 
                u.user_id, 
                u.full_name, 
                u.email, 
                u.account_type, 
                u.contact_number,
                u.status,
                u.created_at,
                COALESCE(ad.access_level) AS accessLevel,
                COALESCE(a.agency_name, au.organization_name) AS organization,
                COALESCE(a.office_code, au.office_code) AS officeCode,
                COALESCE(a.gov_id_number, au.accreditation_number) AS identifier
            FROM users u
            LEFT JOIN agencies a ON u.user_id = a.user_id
            LEFT JOIN auditors au ON u.user_id = au.user_id
            LEFT JOIN admins ad ON u.user_id = ad.user_id
            WHERE u.account_type = ?
        ";

        $params = [$type];
        $types = "s";

        // ✅ Add search filter (if any)
        if (!empty($search_term)) {
            $sql .= "
                AND (
                    u.full_name LIKE ? OR
                    u.email LIKE ? OR
                    a.agency_name LIKE ? OR
                    au.organization_name LIKE ? OR
                    u.contact_number LIKE ? OR
                    a.gov_id_number LIKE ? OR
                    au.accreditation_number LIKE ?
                )
            ";
            $search_like = '%' . $search_term . '%';
            // add all 7 bindings for the search
            $params = array_merge($params, array_fill(0, 7, $search_like));
            $types .= str_repeat("s", 7);
        }

        // ✅ Add order and pagination
        $sql .= " ORDER BY $order_by $order_dir LIMIT 5 OFFSET ?";

        $params[] = $offset;
        $types .= "i";

        // ✅ Prepare and execute query safely
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result();
    }

    /**
     * Get counters: total users, approved, pending, agency, auditor, citizen
     */
    public function getUserCounters()
    {
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
