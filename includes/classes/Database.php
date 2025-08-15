<?php

class Database {
    // Admin Methods
    public function getAdminById($id) {
        $stmt = $this->connection->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAdminByEmail($email) {
        $stmt = $this->connection->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateAdmin($id, $data) {
        $fields = [];
        $types = "";
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
            $types .= $this->getParamType($value);
        }
        
        $values[] = $id;
        $types .= "i";
        
        $sql = "UPDATE admins SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function deleteAdmin($id) {
        $stmt = $this->connection->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Admin Activity Methods
    public function logAdminActivity($admin_id, $action, $details) {
        $stmt = $this->connection->prepare(
            "INSERT INTO admin_activities (admin_id, action, details, ip_address) 
             VALUES (?, ?, ?, ?)"
        );
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("isss", $admin_id, $action, $details, $ip);
        return $stmt->execute();
    }

    public function getAdminActivities($admin_id, $page = 1, $per_page = 20) {
        $offset = ($page - 1) * $per_page;
        $stmt = $this->connection->prepare(
            "SELECT * FROM admin_activities 
             WHERE admin_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?, ?"
        );
        $stmt->bind_param("iii", $admin_id, $offset, $per_page);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Helper method for determining parameter type
    private function getParamType($value) {
        if (is_int($value)) return "i";
        if (is_double($value)) return "d";
        return "s";
    }
} 