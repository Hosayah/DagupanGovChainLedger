// Load ABI + Contract Address dynamically
let contract, signer;

async function init() {
  if (!window.ethereum) {
    alert("Please install MetaMask!");
    return;
  }

  const provider = new ethers.BrowserProvider(window.ethereum);
  await provider.send("eth_requestAccounts", []); // make sure accounts are requested
  signer = await provider.getSigner();

  const abi = await fetch("./abi.json").then(res => res.json());
  const contractData = await fetch("./contract-address.json").then(res => res.json());
  const contractAddress = contractData.address;

  contract = new ethers.Contract(contractAddress, abi, signer);
  console.log(contractAddress);
  console.log(signer.getAddress());

  // Check if it was granted
  /*
  const hasRole = await contract.hasRole(
    ethers.id("GOV_AGENCY_ROLE"),
    await signer.getAddress()
  );
  console.log("Role check:", hasRole); // true = success
  */
  console.log("✅ Contract initialized:", contractAddress);
  document.getElementById("walletAddress").innerText =
    `Connected: ${await signer.getAddress()}`;
}

// Submit Spending Record
async function addSpending() {
  const docHash = document.getElementById("docHash").value;
  const recordType = document.getElementById("recordType").value;
  const amount = document.getElementById("amount").value;

  try {
    const tx = await contract.submitSpendingRecord(
      ethers.id(docHash), // Convert string to bytes32 hash
      recordType,
      amount
    );
    await tx.wait();
    alert("✅ Spending record added!");
  } catch (err) {
    console.error(err);
    alert("❌ Error adding spending");
  }
}

// Submit Audit
async function addAudit() {
  const recordId = document.getElementById("auditRecordId").value;
  const notes = document.getElementById("auditNotes").value;

  try {
    const tx = await contract.submitAudit(recordId, notes);
    await tx.wait();
    alert("✅ Audit submitted!");
  } catch (err) {
    console.error(err);
    alert("❌ Error submitting audit");
  }
}

// Get Counts
async function getCounts() {
  if (!contract) {
    alert("Contract not initialized yet!");
    return;
  }

  try {
    let spendingCount = 0;
    let auditCount = 0;
    const recordId = 1;

    // Only call if the functions exist
    if (contract.getSpendingCount) {
      spendingCount = await contract.getSpendingCount();
    }
    if (contract.getAuditCount) {
     // auditCount = await contract.getAuditCount(recordId);
    }
    
    document.getElementById("counts").innerText =
      `Spending Records: ${spendingCount}\nAudits: ${auditCount}`;
  } catch (err) {
    console.error("Error fetching counts:", err);
    document.getElementById("counts").innerText =
      "Could not fetch counts. Make sure contract is deployed and initialized.";
  }
}

async function fetchRecord(recordId) {
  try {
    const record = await contract.getRecord(recordId);

    // Convert the result
    const recordData = {
      recordId: record.recordId.toString(),
      documentHash: record.documentHash,
      recordType: record.recordType,
      amount: record.amount, // if you stored in wei
      submittedBy: record.submittedBy,
      timestamp: new Date(Number(record.timestamp) * 1000).toLocaleString()
    };

    renderRecord(recordData);
  } catch (error) {
    console.error("Error fetching record:", error);
  }
}


// Run init on load
window.onload = init;
window.addSpending = addSpending;
window.addAudit = addAudit;
window.getCounts = getCounts;
window.fetchRecord = fetchRecord;

