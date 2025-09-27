const { ethers } = require("hardhat");

async function main() {
  const [deployer, govAgency] = await ethers.getSigners();
  const contractAddress = "YOUR_DEPLOYED_CONTRACT_ADDRESS"; // replace with actual

  const ledger = await ethers.getContractAt("GovSpendingLedger", contractAddress);

  // Sample spending record
  const projectId = "P001";
  const amount = 5000;
  const vendor = "EcoBuild Supplies";
  const invoice = "INV-12345";

  const tx = await ledger.connect(govAgency).submitSpendingRecord(
    projectId,
    amount,
    vendor,
    invoice
  );
  await tx.wait();

  console.log("Spending record submitted by:", govAgency.address);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
