const { ethers } = require("hardhat");

async function main() {
  const [deployer, govAgency, auditor] = await ethers.getSigners();

  // Replace with your deployed contract address
  const contractAddress = "0x5FbDB2315678afecb367f032d93F642f64180aa3";

  const GovSpendingLedger = await ethers.getContractFactory("GovSpendingLedger");
  const contract = GovSpendingLedger.attach(contractAddress);

  console.log("Adding dummy spending record...");

  // Dummy spending record
  const docHash = ethers.id("dummy-contract-1"); // Convert string to bytes32
  const recordType = "budget";
  const amount = 1000;

  // Connect as GOV_AGENCY_ROLE account
  const govContract = contract.connect(govAgency);
  const tx1 = await govContract.submitSpendingRecord(docHash, recordType, amount);
  await tx1.wait();

  console.log("✅ Dummy spending record added!");

  console.log("Adding dummy audit...");

  const auditNotes = "Audit completed successfully";
  const auditContract = contract.connect(auditor);
  const tx2 = await auditContract.submitAudit(1, auditNotes); // recordId = 1
  await tx2.wait();

  console.log("✅ Dummy audit added!");
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
