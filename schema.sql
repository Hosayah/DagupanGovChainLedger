-- =====================
-- USERS TABLE
-- =====================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- bcrypt/argon2 hashed password
    role ENUM('gov_agency', 'auditor') NOT NULL,
    wallet_address VARCHAR(42) NOT NULL UNIQUE, -- Ethereum address
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================
-- PROJECTS TABLE
-- =====================
CREATE TABLE projects (
    project_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL, -- e.g. Education, Healthcare, Infrastructure
    description TEXT,
    created_by INT NOT NULL, -- FK to users.id
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- =====================
-- RECORDS TABLE
-- =====================
CREATE TABLE records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL, -- FK to projects.project_id
    record_type ENUM('budget', 'invoice', 'contract') NOT NULL,
    amount DECIMAL(18, 2) NOT NULL,
    document_path VARCHAR(255), -- file path in server
    document_hash CHAR(66) NOT NULL, -- 0x-prefixed SHA256 hash
    blockchain_tx VARCHAR(66), -- 0x-prefixed tx hash
    submitted_by INT NOT NULL, -- FK to users.id
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id),
    FOREIGN KEY (submitted_by) REFERENCES users(id)
);
-- ===============================
-- 👉 Do you want me to also sketch the backend API routes (Node.js + Express) 
-- that connect your frontend → MySQL → blockchain? That way your GovAgency and Auditor can interact seamlessly.
-- ===============================================================================