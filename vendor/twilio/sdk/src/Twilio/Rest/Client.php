<?php
namespace Twilio\Rest;
use Twilio\Domain;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Http\Client as HttpClient;
use Twilio\Rest\Api\V2010;
class Client extends Domain {
    protected $username;
    protected $password;
    protected $accountSid;
    protected $region;
    protected $edge;
    protected $_account;
    public function __construct(
        string $username,
        string $password,
        string $accountSid = null,
        string $region = null,
        HttpClient $httpClient = null,
        array $config = []
    ) {
        if (!$username || !$password) {
            throw new ConfigurationException('Credentials are required to create a Client');
        }
        parent::__construct($this);
        $this->username = $username;
        $this->password = $password;
        $this->accountSid = $accountSid ?: $username;
        $this->region = $region;
        $this->httpClient = $httpClient;
        $this->config = $config;
    }
    // Additional methods...
}