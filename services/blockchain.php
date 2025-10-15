<?php
require __DIR__ . '/../vendor/autoload.php';  // Adjusted path
use kornrunner\Keccak;
use phpseclib\Math\BigInteger;
use Web3\Web3;
use Web3\Contract;

$rpcUrl = 'http://127.0.0.1:7545';
$web3 = new Web3($rpcUrl);

$abi = file_get_contents(__DIR__.'/abi.json');
$contractData = json_decode(file_get_contents(__DIR__.'/contract-address.json'), true);
$contractAddress = $contractData['address'];
$contract = new Contract($web3->provider, $abi);
$contract->at($contractAddress);

$adminWallet = "0x59183A5dc4C8F3E70F9599052af541d2f6f6c673";

// Compute role hash
$govRole = "0x" . Keccak::hash('GOV_AGENCY_ROLE', 256);
$auditorRole = "0x" . Keccak::hash('AUDITOR_ROLE', 256);

//echo "✅ Contract initialized: {$contractAddress}<br>";

// ---- Functions ----
function getCounts($contract)
{
    $count = null;

    $contract->call('getSpendingCount', function ($err, $result) use (&$count) {
        if ($err !== null) {
            $count = 0; // fallback on error
        } else {
            // handle BigInteger
            $val = $result[0];
            if (is_object($val) && property_exists($val, 'value')) {
                $count = hexdec($val->value);
            } else {
                $count = intval($val);
            }
        }
    });

    return (int) $count;
}


function submitSpending($contract, $from, $docHash, $recordType){
    $txResult = null;
    $contract->send(
        'submitSpendingRecord',
        $docHash,
        $recordType,
        [
            'from' => $from,
            'gas' => '0x4C4B40', // 5,000,000 gas
            'gasPrice' => '0x3b9aca00' // 1 Gwei
        ],
        function ($err, $tx) use (&$txResult) {
            if ($err !== null) {
                $txResult = "❌ Error: " . $err->getMessage();
                return;
            }
            $txResult = $tx; // store tx hash instead of echo
        }
    );
    usleep(200000); // 0.2 sec
    return $txResult; 
}

function addGovAgency($contract, $adminWallet, $agencyWallet){
    $contract->send(
        'addGovAgency',
        $agencyWallet,
        [
            'from' => $adminWallet,
            'gas' => '0x4C4B40',
            'gasPrice' => '0x3b9aca00'
        ],
        function ($err, $tx) {
            if ($err) echo "❌ Error: " . $err->getMessage();
            else echo "<script type='text/javascript'>alert('✅ Tx sent successfully!<br>Tx Hash: {$tx}');</script>";
        }
    );
}
function addAuditor($contract, $adminWallet, $agencyWallet){
    $contract->send(
        'addAuditor',
        $agencyWallet,
        [
            'from' => $adminWallet,
            'gas' => '0x4C4B40',
            'gasPrice' => '0x3b9aca00'
        ],
        function ($err, $tx) {
            if ($err) echo "❌ Error: " . $err->getMessage();
            else echo "<script type='text/javascript'>alert('✅ Tx sent successfully!<br>Tx Hash: {$tx}');</script>";
        }
    );
}

//addGovAgency($contract, "0x59183A5dc4C8F3E70F9599052af541d2f6f6c673", "0xdac457007A38eA2d992FBfcA1d07fC36d1210bfD");
// ---- Example calls ----
//getCounts($contract);

function getBalance($web3, $address){
    $web3->eth->getBalance($address, function ($err, $balance) {
        if ($err !== null) {
            echo "❌ Error getting balance: " . $err->getMessage();
        } else {
            echo "💰 Wallet balance: " . $balance->toString() . " wei<br>";
        }
    });
}


function hasRole($contract, $role, $from)
{
    $resultBool = false;
    // Important: web3.php allows single params as separate args
    $contract->call('hasRole', $role, $from, function ($err, $result) use (&$resultBool) {
        if ($err !== null) {
            $resultBool = false;
            return;
        }

        // Handle cases: result may be array([0] => bool) or just bool
        if (is_array($result) && isset($result[0])) {
            $resultBool = (bool)$result[0];
        } else {
            $resultBool = (bool)$result;
        }
    });

    return $resultBool;
}


//hasRole($contract, $govRole, $from);

//submitSpending($contract, '0x23E093083F66AfbC1882dAA72BA5Eb0C4DA5e1c8', '0x' . hash('sha256', 'Document'), 'Balance');


function normalizeValue($val) {
    // Arrays (e.g. hex fragments)
    if (is_array($val)) {
        $out = '';
        foreach ($val as $v) {
            $out .= normalizeValue($v);
        }
        return $out;
    }

    // Objects (phpseclib BigInteger etc.)
    if (is_object($val)) {
        // phpseclib BigInteger -> try toString()
        if (method_exists($val, 'toString')) {
            return $val->toString(); // decimal string
        }

        // Some BigInteger representations expose a 'value' property like "0x..."
        if (property_exists($val, 'value')) {
            $v = $val->value;
            if (is_string($v) && strpos($v, '0x') === 0) {
                // Convert hex to decimal using BigInteger
                $hex = substr($v, 2);
                try {
                    $big = new BigInteger($hex, 16);
                    return $big->toString();
                } catch (Exception $e) {
                    return $v; // fallback to raw hex
                }
            }
            return (string)$v;
        }

        // Fallback
        return (string)$val;
    }

    // Scalar values (string, int)
    return (string)$val;
}

/**
 * Fetch a record and return a PHP associative array via callback.
 *
 * @param Web3\Contract $contract  Initialized Contract instance
 * @param int|string    $recordId  Record id to query (uint256)
 * @param callable      $cb        function(array $map) { ... }  // will be invoked with map or ['error'=>...]
 */
function getRecordAsMap($contract, $recordId, callable $cb)
{
    // Note: web3.php expects params as separate args, not an array (for 1 arg it's ok to pass $recordId directly)
    $contract->call('getRecordBasic', $recordId, function ($err, $result) use ($cb) {
        if ($err !== null) {
            $cb(['error' => $err->getMessage()]);
            return;
        }

        // Sanity check
        if (!is_array($result)) {
            $cb(['error' => 'Unexpected contract return format.']);
            return;
        }

        // Map fields according to your Solidity return:
        // (uint256, bytes32, string, uint256, address, uint256)
        $map = [];
        $map['record_id']    = isset($result[0]) ? normalizeValue($result[0]) : null;
        $map['doc_hash']     = isset($result[1]) ? normalizeValue($result[1]) : null;
        $map['record_type']  = isset($result[2]) ? normalizeValue($result[2]) : null;
        //$map['amount_wei']   = isset($result[3]) ? normalizeValue($result[3]) : '0';
        $map['submitted_by'] = isset($result[4]) ? normalizeValue($result[3]) : null;
        $map['timestamp']    = isset($result[5]) ? normalizeValue($result[4]) : '0';

        // Convert numeric strings to nicer formats
        // timestamp -> ISO date (if > 0)
        $ts = intval($map['timestamp']);
        $map['date'] = ($ts > 0) ? date('c', $ts) : null;

        // amount wei -> amount eth (use bcdiv if available)
        /*if (is_numeric($map['amount_wei']) && function_exists('bcdiv')) {
            // keep 18 decimals
            $map['amount_eth'] = bcdiv($map['amount_wei'], '1000000000000000000', 18);
        } elseif (is_numeric($map['amount_wei'])) {
            // fallback float (may lose precision)
            $map['amount_eth'] = (float)$map['amount_wei'] / 1e18;
        } else {
            $map['amount_eth'] = null;
        }*/
        $cb($map);
    });
}
function getRecordAsArray($contract, $recordId)
{
    $result = null;
    $contract->call('getRecordBasic', $recordId, function ($err, $res) use (&$result) {
        if ($err !== null) {
            $result = ['error' => $err->getMessage()];
        } else {
            $map = [];
            $map['record_id']    = normalizeValue($res[0]);
            $map['doc_hash']     = normalizeValue($res[1]);
            $map['record_type']  = normalizeValue($res[2]);
            //$map['amount_wei']   = normalizeValue($res[3]);
            $map['submitted_by'] = normalizeValue($res[3]);
            $map['timestamp']    = normalizeValue($res[4]);
            //$map['amount_eth']   = bcdiv($map['amount_wei'], '1000000000000000000', 18);
            $map['date']         = date('c', intval($map['timestamp']));
            $result = $map;
        }
    });
    return $result;
}

//$record = getRecordAsArray($contract, 1);
//print_r($record);

function submitAudit($contract, $from, $recordId, $docHash, $result)
{
    $txResult = null;

    // Solidity Enum AuditResult { PASSED=0, FLAGGED=1, REJECTED=2 }
    $contract->send(
        'submitAudit',
        (int)$recordId,
        $docHash,
        (int)$result,
        [
            'from' => $from,
            'gas' => '0x4C4B40',
            'gasPrice' => '0x3b9aca00'
        ],
        function ($err, $tx) use (&$txResult) {
            if ($err !== null) {
                $txResult = "❌ Error: " . $err->getMessage();
                return;
            }
            $txResult = $tx;
        }
    );

    usleep(200000);
    return $txResult;
}
error_reporting(0);
ini_set('display_errors', 0);
function getAuditsAsArray($contract, $recordId)
{
    $result = [];

    $contract->call('getAudits', $recordId, function ($err, $res) use (&$result) {
        if ($err !== null) {
            $result = ['error' => $err->getMessage()];
            return;
        }

        if (!is_array($res) || count($res) === 0) {
            $result = [];
            return;
        }

        // Detect flat array (e.g. length % 5 == 0 for 5 struct fields)
        $flat = !is_array($res[0]);
        $fieldsPerStruct = 5; // recordId, docHash, result, auditor, timestamp
        $audits = [];

        if ($flat) {
            $chunks = array_chunk($res, $fieldsPerStruct);
            foreach ($chunks as $chunk) {
                $entry = [];
                $entry['record_id'] = isset($chunk[0]) ? normalizeValue($chunk[0]) : null;
                $entry['doc_hash']  = isset($chunk[1]) ? normalizeValue($chunk[1]) : null;
                $entry['result']    = isset($chunk[2]) ? normalizeValue($chunk[2]) : null;
                $entry['auditor']   = isset($chunk[3]) ? normalizeValue($chunk[3]) : null;
                $entry['timestamp'] = isset($chunk[4]) ? normalizeValue($chunk[4]) : '0';

                $ts = intval($entry['timestamp']);
                $entry['date'] = ($ts > 0) ? date('c', $ts) : null;

                $audits[] = $entry;
            }
        } else {
            // Nested array (standard struct decoding)
            foreach ($res as $r) {
                if (!is_array($r)) continue;

                $entry = [];
                $entry['record_id'] = isset($r[0]) ? normalizeValue($r[0]) : null;
                $entry['doc_hash']  = isset($r[1]) ? normalizeValue($r[1]) : null;
                $entry['result']    = isset($r[2]) ? normalizeValue($r[2]) : null;
                $entry['auditor']   = isset($r[3]) ? normalizeValue($r[3]) : null;
                $entry['timestamp'] = isset($r[4]) ? normalizeValue($r[4]) : '0';

                $ts = intval($entry['timestamp']);
                $entry['date'] = ($ts > 0) ? date('c', $ts) : null;

                $audits[] = $entry;
            }
        }

        $result = $audits;
    });

    return $result;
}

//$data = getAuditsAsArray($contract, 1);

//echo print_r($data[0], true);



