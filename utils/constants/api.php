<?php
//require '../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Now you can access them like this:
class ApiKey{
    private $geminiAPI;
    private $ipfsAPI;
    private $mailPassword;

    public function __construct() {
        $this->geminiAPI = $_ENV['GEMINI_API_KEY'] ?? '';
        $this->ipfsAPI = $_ENV['IPFS_JWT'] ?? '';
        $this->mailPassword = $_ENV['MAIL'] ?? '';
    }

    public function getGeminiApi(): string{
        return $this->geminiAPI;
    }
    public function getIpfsApi(): string{
        return $this->ipfsAPI;
    }
    public function getMailPass(): string{
        return $this->mailPassword;
    }
}






