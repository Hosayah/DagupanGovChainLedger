const express = require("express");
const session = require("express-session");
const bodyParser = require("body-parser");
const cors = require("cors");
const path = require("path");

const app = express();
const PORT = 3000;

// Middleware
app.use(cors({
  origin: ["http://localhost:5500", "http://127.0.0.1:5500"],// change this to your frontend addres
  credentials: true
}));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(session({
  secret: "govledger-secret",
  resave: false,
  saveUninitialized: false,
  cookie: {
    secure: false,      // true if HTTPS
    httpOnly: true,     // prevents JS access
    sameSite: "lax",   // allow cookies across localhost
    maxAge: 1000 * 60 * 60 // 1 hour
  }
}));

// Routes
const authRoutes = require("./routes/auth");
app.use("/auth", authRoutes);

// Serve static frontend
app.use(express.static(path.join(__dirname, "frontend")));

// Catch-all (optional, for SPA-style routing)
app.get("/", (req, res) => {
  res.sendFile(path.join(__dirname, "frontend", "index.html"));
});

app.get("/test", (req, res) => {
  res.sendFile(path.join(__dirname, "frontend", "test.html"))
});

app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});
