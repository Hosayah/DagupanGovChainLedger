require("@nomicfoundation/hardhat-toolbox");
require("dotenv").config(); 

/** @type import('hardhat/config').HardhatUserConfig */
module.exports = {
  solidity: "0.8.28",
    networks: {
      local: {
        url: "http://127.0.0.1:7545",
        chainId: 1337,
        accounts: [
          "0xbf876007d9d2b0dfa6001696366fdcfcd488c30ca27dbad5a68ccebed363b5e7",
          "0x7c54ae0f05220a4f668f55f06465681e7867f3a2ba945bf81102ce3cb1bf2546",
          "0xc060c2c38b3e423586f301b3f66106d057963ddcf56dcf0364f77ef28e280792"
        ] // optional
      }
    }
};
