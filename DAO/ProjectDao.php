<?php
class ProjectDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // get all users
    public function getAllProjects($limit = 0): mixed {
        $sql = "
            SELECT * FROM projects ORDER BY created_at DESC LIMIT 5 OFFSET ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result;
    }
    public function getAllProjectsWithSearch($limit = 0, $search_term = ''): mixed
    {
        // Base SQL
        $sql = "SELECT * FROM projects WHERE 1=1";

        $params = [];
        $types = "";

        // ✅ Add search filter if a search term is provided
        if (!empty($search_term)) {
            $sql .= " AND (title LIKE ? OR category LIKE ? OR description LIKE ?)";
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

        // Prepare and bind dynamically
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        return $stmt->get_result();
    }

    public function getProjectByUserId($id, $limit = 0): mixed {
        $sql = "
            SELECT * FROM projects WHERE created_by = ? ORDER BY created_at DESC LIMIT 5 OFFSET ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $limit);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result; 
    }
    public function getProjectByUserIdWithSearch($user_id, $limit = 0, $search_term = ''): mixed {
        $sql = "
            SELECT 
                project_id,
                title,
                category,
                description,
                document_path,
                created_by,
                created_at
            FROM projects
            WHERE created_by = ?
        ";

        $params = [$user_id];
        $types = "i";

        // ✅ Optional search term filter
        if (!empty($search_term)) {
            $sql .= " 
                AND (
                    title LIKE ? 
                    OR category LIKE ? 
                    OR description LIKE ?
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

        // ✅ Prepare and execute
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->get_result();
}


    /**
     * Get latest projects (default 5)
     */
    public function getProjectByTitle($title, $limit = 0): mixed {
        $sql = "
            SELECT * FROM projects WHERE title = ? LIMIT 5 OFFSET ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $title, $limit);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result->fetch_assoc(); 
    }
    public function getProjectById($id): mixed {
        $sql = "SELECT * FROM projects WHERE project_id = ?;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result(); // get mysqli_result
        return $result->fetch_assoc(); // store the result as an associative array 
    }
    
    /**
     * Get counters: total users, approved, pending, agency, auditor, citizen
     */
    public function updateProject($title, $category, $description, $project_id): mixed {
        $sql = "
          UPDATE projects SET title= ?, category = ?, description = ? WHERE project_id = ?;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $title, $category, $description, $project_id);
        return $stmt->execute();
    }
    public function getProjectCounters($id) {
        $data = [
            "total" => 0,
            "orgTotal" => 0,
        ];

        // Total projects
        $res = $this->conn->query("SELECT COUNT(project_id) AS count FROM projects");
        if ($res) {
            $row = $res->fetch_assoc();
            $data["total"] = $row["count"];
        }

        // Projects count per Agency
        $res = $this->conn->prepare("SELECT COUNT(project_id) AS count FROM projects WHERE created_by = ?");
        $res->bind_param("i", $id);
        if ($res->execute()) {
            $result = $res->get_result();
            while ($row = $result->fetch_assoc()) {
                $data["orgTotal"] = $row["count"];
            }
        }
        
        return $data;
    }
}
