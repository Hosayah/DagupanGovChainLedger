const { ethers } = require("hardhat");

async function main() {
  const [deployer, govAgency, auditor] = await ethers.getSigners();
  const contractAddress = "YOUR_DEPLOYED_CONTRACT_ADDRESS"; // replace with actual

  const ledger = await ethers.getContractAt("GovSpendingLedger", contractAddress);

  // Sample audit record
  const projectId = "P001";
  const findings = "Audit complete: all expenses verified.";

  const tx = await ledger.connect(auditor).submitAudit(projectId, findings);
  await tx.wait();

  console.log("Audit submitted by:", auditor.address);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
