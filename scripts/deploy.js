// scripts/deploy.js
const { ethers } = require("hardhat");
const fs = require("fs");
const path = require("path");

async function main() {
  const [deployer, govAgency, auditor] = await ethers.getSigners();

  console.log("Deploying contracts with account:", deployer.address);

  const GovSpendingLedger = await ethers.getContractFactory("GovSpendingLedger");
  const ledger = await GovSpendingLedger.deploy();
  await ledger.waitForDeployment();

  const contractAddress = await ledger.getAddress();
  console.log("GovSpendingLedger deployed to:", contractAddress);

  // Define role constants (must match contract)
 // const GOV_AGENCY_ROLE = ethers.id("GOV_AGENCY_ROLE");
  //const AUDITOR_ROLE = ethers.id("AUDITOR_ROLE");

  // Assign roles
  //await ledger.grantRole(GOV_AGENCY_ROLE, govAgency.address);
  //await ledger.grantRole(AUDITOR_ROLE, auditor.address);

  console.log(`Granted GOV_AGENCY_ROLE to ${govAgency.address}`);
  console.log(`Granted AUDITOR_ROLE to ${auditor.address}`);

  // Save ABI + address for frontend
  const abiDir = path.join(__dirname, "../services");

  if (!fs.existsSync(abiDir)) {
    fs.mkdirSync(abiDir);
  }

  // Save ABI
  const artifact = await hre.artifacts.readArtifact("GovSpendingLedger");
  fs.writeFileSync(
    path.join(abiDir, "abi.json"),
    JSON.stringify(artifact.abi, null, 2)
  );

  // Save contract address
  fs.writeFileSync(
    path.join(abiDir, "contract-address.json"),
    JSON.stringify({ address: contractAddress }, null, 2)
  );

  console.log("ABI and contract address saved to services/");
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
