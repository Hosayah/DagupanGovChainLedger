// scripts/ipfsUpload.js
const { create } = require("ipfs-http-client");
const fs = require("fs");
require("dotenv").config();

async function main() {
  // Connect to IPFS (using Infura endpoint or local node)
  const ipfs = create({
    host: "ipfs.infura.io",
    port: 5001,
    protocol: "https",
    headers: {
      authorization: `Basic ${Buffer.from(`${process.env.INFURA_PROJECT_ID}:${process.env.INFURA_PROJECT_SECRET}`).toString('base64')}`
    }
  });

  // Read local file to upload
  const filePath = "./documents/contract123.pdf";
  const file = fs.readFileSync(filePath);

  const result = await ipfs.add(file);
  console.log("File uploaded to IPFS. CID:", result.cid.toString());
}

main().catch(console.error);
