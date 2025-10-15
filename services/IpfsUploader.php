<?php
class PinataUploader {
    private string $jwt;
    private string $endpoint = "https://api.pinata.cloud/pinning/pinFileToIPFS";
    private string $gateway = "https://plum-actual-elephant-371.mypinata.cloud/ipfs/";

    public function __construct(string $jwt) {
        $this->jwt = $jwt;
    }

    /**
     * Upload a document to Pinata IPFS.
     * @param array $file The $_FILES['document'] array.
     * @return string The CID (IpfsHash) of the uploaded file.
     * @throws Exception if upload fails.
     */
    public function uploadDocument(array $file): string {
        if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            throw new Exception("No valid file uploaded.");
        }

        $filePath = $file['tmp_name'];
        $fileName = $file['name'];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->jwt}"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'file' => new CURLFile($filePath, mime_content_type($filePath), $fileName)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception("cURL Error: $curlError");
        }

        if ($httpCode == 429) {
            throw new Exception("⚠️ Pinata rate limit hit — wait a few seconds and retry.");
        }

        $result = json_decode($response, true);

        if (!isset($result['IpfsHash'])) {
            throw new Exception("Upload failed. Response: " . $response);
        }

        return $result['IpfsHash']; // CID
    }

    /**
     * Get the full gateway URL for a given CID.
     */
    public function getGatewayUrl(string $cid): string {
        return $this->gateway . $cid;
    }
}
?>
