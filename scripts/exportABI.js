const fs = require("fs");
const path = require("path");

async function main() {
  const contractName = "GovSpendingLedger";
  const artifactsPath = path.join(__dirname, `../artifacts/contracts/${contractName}.sol/${contractName}.json`);
  const frontendDir = path.join(__dirname, "../frontend");

  if (!fs.existsSync(artifactsPath)) {
    console.error(`❌ ABI not found at: ${artifactsPath}`);
    process.exit(1);
  }

  const artifact = JSON.parse(fs.readFileSync(artifactsPath, "utf8"));
  const abi = artifact.abi;

  if (!fs.existsSync(frontendDir)) {
    fs.mkdirSync(frontendDir, { recursive: true });
  }

  const abiPath = path.join(frontendDir, "abi.json");
  fs.writeFileSync(abiPath, JSON.stringify(abi, null, 2));

  console.log(`✅ ABI exported to ${abiPath}`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
