const express = require("express");
const session = require("express-session");
const bodyParser = require("body-parser");
const cors = require("cors");

const app = express();

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
  saveUninitialized: true,
  cookie: { secure: false } // set true if HTTPS
}));

// Routes
const authRoutes = require("./routes/auth");
app.use("/auth", authRoutes);

app.listen(3000, () => console.log("🚀 Server running on port 3000"));
