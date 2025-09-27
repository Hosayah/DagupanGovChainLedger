const { ethers } = require("hardhat");

async function main() {
    const contractAddress = "0xb6b41394ca36174f2C913Ee9E78Ca2B04C7FEE18";
    const GovSpendingLedger = await ethers.getContractFactory("GovSpendingLedger");
    const ledger = await GovSpendingLedger.attach(contractAddress);

    const record = await ledger.getRecord(1);
    console.log("Record 1:", record);

    const audits = await ledger.getAudits(1);
    console.log("Audits for Record 1:", audits);
}

main().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
