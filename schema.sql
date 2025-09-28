-- =========================
-- USERS (Main Account Table)
-- =========================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    account_type ENUM('agency', 'auditor', 'citizen', 'admin') NOT NULL, 
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role VARCHAR(100), -- e.g., Budget Officer, Auditor, Journalist, or Citizen
    contact_number VARCHAR(50),
    office_address TEXT,
    status ENUM('pending', 'approved', 'rejected', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Auto-approval trigger for citizens
DELIMITER $$
CREATE TRIGGER auto_approve_citizen
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.account_type = 'citizen' THEN
        SET NEW.status = 'approved';
    END IF;
END$$
DELIMITER ;

-- =========================
-- GOVERNMENT AGENCIES
-- =========================
CREATE TABLE agencies (
    agency_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    agency_name VARCHAR(255) NOT NULL, -- e.g., DPWH, DepEd
    office_code VARCHAR(50) NOT NULL,
    position VARCHAR(100), -- Position of officer
    gov_id_number VARCHAR(100), -- Employee ID / Government ID
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- =========================
-- AUDITORS / NGOs
-- =========================
CREATE TABLE auditors (
    auditor_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organization_name VARCHAR(255) NOT NULL,
    accreditation_number VARCHAR(100) NOT NULL, -- COA ID / NGO License
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- =========================
-- SYSTEM ADMINS
-- =========================
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    access_level ENUM('super_admin', 'review_admin') DEFAULT 'review_admin',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- =========================
-- PROJECTS
-- =========================
CREATE TABLE projects (
    project_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL, -- Education, Healthcare, Infrastructure
    description TEXT,
    created_by INT NOT NULL, -- FK to users.user_id (agencies)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- =========================
-- RECORDS (Budget, Invoice, Contracts)
-- =========================
CREATE TABLE records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    record_type ENUM('budget', 'invoice', 'contract') NOT NULL,
    amount DECIMAL(18, 2) NOT NULL,
    document_path VARCHAR(255), -- File path
    document_hash CHAR(66) NOT NULL, -- 0x-prefixed SHA256 hash
    blockchain_tx VARCHAR(66), -- 0x-prefixed tx hash
    submitted_by INT NOT NULL, -- FK to users.user_id
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id),
    FOREIGN KEY (submitted_by) REFERENCES users(user_id)
);

-- =========================
-- CITIZEN FEEDBACK
-- =========================
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL, -- Citizen giving feedback
    comment TEXT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5), -- Optional: 1-5 stars
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
