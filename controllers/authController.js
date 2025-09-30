const bcrypt = require("bcryptjs");
const db = require("../config/db");

// Register
exports.register = (req, res) => {
  const { user_type, name, email, password, contact, extra_info } = req.body;

  const hashedPassword = bcrypt.hashSync(password, 10);

  // Insert into users
  const sqlUser = `
    INSERT INTO users (account_type, email, password_hash, full_name, role, contact_number)
    VALUES (?, ?, ?, ?, ?, ?)
  `;

  db.query(
    sqlUser,
    [user_type, email, hashedPassword, name, extra_info?.role || null, contact],
    (err, result) => {
      if (err) {
        console.error(err);
        return res.status(500).json({ msg: "Error registering user" });
      }

      const userId = result.insertId;

      // Insert into role-specific tables
      if (user_type === "agency") {
        const sqlAgency = `
          INSERT INTO agencies (user_id, agency_name, office_code, position, gov_id_number)
          VALUES (?, ?, ?, ?, ?)
        `;
        db.query(
          sqlAgency,
          [userId, name, extra_info.officeCode, extra_info.position, extra_info.govId],
          (err) => {
            if (err) {
              console.error(err);
              return res.status(500).json({ msg: "Error saving agency details" });
            }
            return res.json({ msg: "Agency registered successfully" });
          }
        );
      } else if (user_type === "auditor") {
        const sqlAuditor = `
          INSERT INTO auditors (user_id, organization_name, accreditation_number)
          VALUES (?, ?, ?)
        `;
        db.query(
          sqlAuditor,
          [userId, name, extra_info.accreditation],
          (err) => {
            if (err) {
              console.error(err);
              return res.status(500).json({ msg: "Error saving auditor details" });
            }
            return res.json({ msg: "Auditor registered successfully" });
          }
        );
      } else if (user_type === "citizen") {
        return res.json({ msg: "Citizen registered successfully (auto-approved)" });
      } else {
        return res.json({ msg: "User registered" });
      }
    }
  );
};

// Login
// Login
exports.login = (req, res) => {
  const { email, password } = req.body;

  const sql = "SELECT * FROM users WHERE email = ?";
  db.query(sql, [email], (err, results) => {
    if (err) return res.status(500).json({ msg: "Database error" });
    if (results.length === 0) return res.status(400).json({ msg: "User not found" });

    const user = results[0];

    // Check account status
    if (user.status === "pending") {
      return res.status(403).json({ msg: "Your account is still pending approval." });
    }
    if (user.status === "rejected") {
      return res.status(403).json({ msg: "Your account was rejected." });
    }
    if (user.status === "suspended") {
      return res.status(403).json({ msg: "Your account is suspended." });
    }

    // Compare with password_hash
    if (!bcrypt.compareSync(password, user.password_hash)) {
      return res.status(401).json({ msg: "Invalid credentials" });
    }

    // Store user in session
    req.session.user = {
      id: user.user_id,
      account_type: user.account_type,
      name: user.full_name,
      role: user.role,
      status: user.status
    };
    console.log(req.session.user);
    res.json({ msg: "Login successful", user: req.session.user });
  });
};
// Check current session
exports.checkSession = (req, res) => {
  if (req.session.user) {
    res.json({ loggedIn: true, user: req.session.user });
  } else {
    res.json({ loggedIn: false });
  }
};

// Logout
exports.logout = (req, res) => {
  req.session.destroy();
  res.json({ msg: "Logged out" });
};
