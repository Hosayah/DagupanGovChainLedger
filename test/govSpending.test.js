const { expect } = require("chai");
const { ethers } = require("hardhat");

describe("GovSpendingLedger", function () {
  let ledger;
  let admin, govAgency, auditor, citizen;
  let GOV_AGENCY_ROLE, AUDITOR_ROLE, CITIZEN_ROLE, ADMIN_ROLE;
  const { keccak256, toUtf8Bytes } = ethers; // Ethers v6 utility functions

  beforeEach(async function () {
    [admin, govAgency, auditor, citizen] = await ethers.getSigners();

    const Ledger = await ethers.getContractFactory("GovSpendingLedger");
    ledger = await Ledger.deploy();
    await ledger.waitForDeployment();

    // Fetch role constants from contract
    GOV_AGENCY_ROLE = await ledger.GOV_AGENCY_ROLE();
    AUDITOR_ROLE = await ledger.AUDITOR_ROLE();
    CITIZEN_ROLE = await ledger.CITIZEN_ROLE();
    ADMIN_ROLE = await ledger.DEFAULT_ADMIN_ROLE(); // deployer
  });

  it("Should grant roles correctly", async function () {
    await ledger.connect(admin).grantRole(GOV_AGENCY_ROLE, govAgency.address);
    await ledger.connect(admin).grantRole(AUDITOR_ROLE, auditor.address);
    await ledger.connect(admin).grantRole(CITIZEN_ROLE, citizen.address);

    expect(await ledger.hasRole(GOV_AGENCY_ROLE, govAgency.address)).to.be.true;
    expect(await ledger.hasRole(AUDITOR_ROLE, auditor.address)).to.be.true;
    expect(await ledger.hasRole(CITIZEN_ROLE, citizen.address)).to.be.true;
  });

  it("Gov agency can submit a spending record", async function () {
    await ledger.connect(admin).grantRole(GOV_AGENCY_ROLE, govAgency.address);

    const documentHash = keccak256(toUtf8Bytes("Contract #1"));

    await ledger.connect(govAgency).submitSpendingRecord(documentHash, "contract", 50000);
    const record = await ledger.getRecord(1);

    expect(record.recordId).to.equal(1);
    expect(record.documentHash).to.equal(documentHash);
    expect(record.amount).to.equal(50000);
    expect(record.submittedBy).to.equal(govAgency.address);
  });

  it("Auditor can submit an audit", async function () {
    // Grant roles
    await ledger.connect(admin).grantRole(GOV_AGENCY_ROLE, govAgency.address);
    await ledger.connect(admin).grantRole(AUDITOR_ROLE, auditor.address);

    // Submit a spending record
    const documentHash = keccak256(toUtf8Bytes("Contract #2"));
    await ledger.connect(govAgency).submitSpendingRecord(documentHash, "invoice", 30000);

    // Auditor submits audit
    await ledger.connect(auditor).submitAudit(1, "Verified and correct");

    const audits = await ledger.getAudits(1);
    expect(audits.length).to.equal(1);
    expect(audits[0].notes).to.equal("Verified and correct");
    expect(audits[0].auditor).to.equal(auditor.address);
  });

  it("Should prevent unauthorized roles from submitting", async function () {
    // Citizen has no roles
    const documentHash = keccak256(toUtf8Bytes("Contract #3"));

    await expect(
      ledger.connect(citizen).submitSpendingRecord(documentHash, "budget", 10000)
    ).to.be.revertedWithCustomError(ledger, "AccessControlUnauthorizedAccount")
      .withArgs(citizen.address, GOV_AGENCY_ROLE);

    await expect(
      ledger.connect(citizen).submitAudit(1, "Trying to audit")
    ).to.be.revertedWithCustomError(ledger, "AccessControlUnauthorizedAccount")
      .withArgs(citizen.address, AUDITOR_ROLE);

  });
});
