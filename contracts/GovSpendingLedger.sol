// SPDX-License-Identifier: MIT
pragma solidity ^0.8.28;

import "@openzeppelin/contracts/access/AccessControl.sol";

contract GovSpendingLedger is AccessControl {
    // ---------------------------
    // Roles
    // ---------------------------
    bytes32 public constant GOV_AGENCY_ROLE = keccak256("GOV_AGENCY_ROLE");
    bytes32 public constant AUDITOR_ROLE = keccak256("AUDITOR_ROLE");
    bytes32 public constant CITIZEN_ROLE = keccak256("CITIZEN_ROLE");

    constructor() {
        _grantRole(DEFAULT_ADMIN_ROLE, msg.sender); // Admin deployer
    }

    // ---------------------------
    // Spending Record Struct & Storage
    // ---------------------------
    struct SpendingRecord {
        uint256 recordId;
        bytes32 documentHash; // Hash of contract/invoice/budget file
        string recordType;    // "budget", "invoice", "contract"
        address submittedBy;
        uint256 timestamp;
    }

    mapping(uint256 => SpendingRecord) public spendingRecords;
    uint256 public recordCount = 0;

    // ---------------------------
    // Audit Workflow Struct & Storage
    // ---------------------------
    enum AuditResult { PASSED, FLAGGED, REJECTED }

    struct AuditFinding {
        uint256 recordId;
        bytes32 documentHash;
        AuditResult result;
        address auditor;
        uint256 timestamp;
    }

    mapping(uint256 => AuditFinding[]) public auditFindings;

    // ---------------------------
    // Events
    // ---------------------------
    event RecordSubmitted(
        uint256 indexed recordId,
        address indexed submittedBy,
        bytes32 documentHash
    );

    event AuditSubmitted(
        uint256 indexed recordId,
        address indexed auditor,
        bytes32 documentHash,
        AuditResult result
    );

    // ---------------------------
    // Submit Spending Records
    // ---------------------------
    function submitSpendingRecord(
        bytes32 documentHash,
        string memory recordType
    ) public onlyRole(GOV_AGENCY_ROLE) {
        recordCount += 1;

        spendingRecords[recordCount] = SpendingRecord({
            recordId: recordCount,
            documentHash: documentHash,
            recordType: recordType,
            submittedBy: msg.sender,
            timestamp: block.timestamp
        });

        emit RecordSubmitted(recordCount, msg.sender, documentHash);
    }

    // ---------------------------
    // Submit Audit Findings
    // ---------------------------
    function submitAudit(
        uint256 recordId,
        bytes32 documentHash,
        AuditResult result
    ) public onlyRole(AUDITOR_ROLE) {
        require(recordId > 0 && recordId <= recordCount, "Record does not exist");

        auditFindings[recordId].push(
            AuditFinding({
                recordId: recordId,
                documentHash: documentHash,
                result: result,
                auditor: msg.sender,
                timestamp: block.timestamp
            })
        );

        emit AuditSubmitted(recordId, msg.sender, documentHash, result);
    }

    // ---------------------------
    // View Spending Records
    // ---------------------------
    function getRecordBasic(uint256 recordId)
        public
        view
        returns (
            uint256,
            bytes32,
            string memory,
            address,
            uint256
        )
    {
        SpendingRecord memory r = spendingRecords[recordId];
        return (r.recordId, r.documentHash, r.recordType, r.submittedBy, r.timestamp);
    }

    // ---------------------------
    // View Audit Findings
    // ---------------------------
    function getAuditAt(uint256 recordId, uint256 index)
        public
        view
        returns (
            uint256,
            bytes32,
            AuditResult,
            address,
            uint256
        )
    {
        require(recordId > 0 && recordId <= recordCount, "Record does not exist");
        require(index < auditFindings[recordId].length, "Index out of range");

        AuditFinding memory a = auditFindings[recordId][index];
        return (a.recordId, a.documentHash, a.result, a.auditor, a.timestamp);
    }


    // ---------------------------
    // Helper Getters
    // ---------------------------
    function getSpendingCount() public view returns (uint256) {
        return recordCount;
    }

    function getAuditCount(uint256 recordId) public view returns (uint256) {
        require(recordId > 0 && recordId <= recordCount, "Record does not exist");
        return auditFindings[recordId].length;
    }

    // ---------------------------
    // Role Management
    // ---------------------------
    function addGovAgency(address account) public onlyRole(DEFAULT_ADMIN_ROLE) {
        grantRole(GOV_AGENCY_ROLE, account);
    }

    function addAuditor(address account) public onlyRole(DEFAULT_ADMIN_ROLE) {
        grantRole(AUDITOR_ROLE, account);
    }
}
