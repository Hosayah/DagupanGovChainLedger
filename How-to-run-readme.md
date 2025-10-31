# 📊 DagupanGovLedger
**How to Run**

---

## XAMPP Setup  
- Make sure you have XAMPP installed.  
- Turn on Apache and MySQL.  
- This project should be inside the xampp/htdocs/ dir.  
- Like this: C:\xampp\htdocs\GovSpendingLedger  

---

## Ganache Setup  
- Install **Ganache** on https://archive.trufflesuite.com/ganache/.  
- Open Ganache and Create a workspace, name it whatever you want.  
- Click Start.  
- Go to settings->server then change network id to 1337.   
- Find hardhat.config.js in the root folder.  
- Change the url to ganache rpc server (Example: http://127.0.0.1:7545).  
- Choose one account in the Ganache workspace. Click show keys and copy the private key.  
- Change the private_key inside the accounts.  

---

## Install requirements and Deploy smart contract  
- Open Cmd.
```cmd
cd C:\xampp\htdocs\GovSpendingLedger
npm install
npx hardhat run scripts/deploy.js --network local  
```
- This code will deploy your smart contract in ganache, and will persist.  
- Copy the address where the contract is deployed.  
- It should look like this:  
```cmd
GovSpendingLedger deployed to: 0xc4CBfe3C1ab019998E7bC9C025C3D1f779480c18
```
- Go to services/blockchain.php  
- Go to $adminWallet on line 17 and paste the deployed wallet address  
- $adminWallet = "0xc4CBfe3C1ab019998E7bC9C025C3D1f779480c18";  

---

## Running Gemini  
- Run this in cmd. You should be in xampp/htdocs/GovSpendingLedger  
```cmd
node server.js  
```

---

## Open in browser
- Go to your browser, then go to -> http://localhost/GovSpendingLedger/public/  

---

## User Credentials
# Superadmin  
Email: superadmin@gov.ph  
Password: admin123  

# Citizen  
Email: catabayjosiah19@gmail.com  
Password: josiah193  

# For GovAgency and Auditor accounts:  
Old accounts are using testing wallet address from ganache, only wallet address from ganache will work for this system.  
You must create new agency and auditor accounts using wallet address from your ganache workspace.  
