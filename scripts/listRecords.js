const { ethers } = require("hardhat");

async function main() {
  const contractAddress = "0xb6b41394ca36174f2C913Ee9E78Ca2B04C7FEE18"; // replace with actual deployed address
 
  
  // Get contract instance
  const ledger = await ethers.getContractAt("GovSpendingLedger", contractAddress);

  // --- Fetch Spending Records ---
  console.log("\n📌 Fetching Spending Records...");
  const spendingCount = await ledger.getSpendingCount();

  for (let i = 0; i < spendingCount; i++) {
    const record = await ledger.spendingRecords(i);
    console.log(`
      🏗️ Project ID: ${record.projectId}
      💰 Amount: ${record.amount.toString()}
      🏢 Vendor: ${record.vendor}
      📑 Invoice: ${record.invoice}
      ⏰ Timestamp: ${record.timestamp}
      🧾 Submitted By: ${record.submittedBy}
    `);
  }

  // --- Fetch Audits ---
  console.log("\n📌 Fetching Audits...");
  const auditCount = await ledger.getAuditCount();

  for (let i = 0; i < auditCount; i++) {
    const audit = await ledger.audits(i);
    console.log(`
      🏗️ Project ID: ${audit.projectId}
      📋 Findings: ${audit.findings}
      ⏰ Timestamp: ${audit.timestamp}
      🔍 Submitted By: ${audit.submittedBy}
    `);
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
