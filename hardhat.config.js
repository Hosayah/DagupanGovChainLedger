require("@nomicfoundation/hardhat-toolbox");

/** @type import('hardhat/config').HardhatUserConfig */
module.exports = {
  solidity: "0.8.28",
    networks: {
      local: {
        url: "http://127.0.0.1:7545",
        chainId: 1337,
        accounts: [
          "0x090756889b386eb59db5b64f6770b9d360d063f2d13fc35592fd310a974b143f",
          "0xe5d9f64a8e8d08704af0c1ee5a2f86c1c1fdddb16d1d9ec7dfdfeb415f09ab7f",
          "0x274af4deee8cf033d711492898a00f10481fd04b25947d651b3c76b51e5f080a"
        ] // optional
      }
    }
};
