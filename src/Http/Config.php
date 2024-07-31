<?php declare(strict_types=1);

namespace PCDB\Http;

use Dotenv\Dotenv;
use PCDB\Exceptions\PCDBException;

/**
 * Config class
 *
 * Manages environment variables and configuration settings.
 */
class Config
{
    private string $_apiKey;
    private string $_environment;
    private string $_apiVersion;
    private string $_customEndpoint;

    /**
     * Config constructor.
     * Loads environment variables from .env file.
     *
     * @throws PCDBException
     */
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        if (!isset($_ENV['API_KEY']) || !is_string($_ENV['API_KEY'])) {
            throw new PCDBException('API_KEY is not set or is not a string');
        }
        if (!isset($_ENV['ENVIRONMENT']) || !is_string($_ENV['ENVIRONMENT'])) {
            throw new PCDBException('ENVIRONMENT is not set or is not a string');
        }
        if (!isset($_ENV['API_VERSION']) || !is_string($_ENV['API_VERSION'])) {
            throw new PCDBException('API_VERSION is not set or is not a string');
        }
        if (!isset($_ENV['CUSTOM_ENDPOINT']) || !is_string($_ENV['CUSTOM_ENDPOINT'])) {
            throw new PCDBException('CUSTOM_ENDPOINT is not set or is not a string');
        }

        $this->_apiKey = $_ENV['API_KEY'];
        $this->_environment = $_ENV['ENVIRONMENT'];
        $this->_apiVersion = $_ENV['API_VERSION'];
        $this->_customEndpoint = $_ENV['CUSTOM_ENDPOINT'];
    }

    /**
     * Get the API key.
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->_apiKey;
    }

    /**
     * Get the environment name.
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->_environment;
    }

    /**
     * Get the Custom Endpoint version.
     *
     * @return string
     */
    public function getCustomEndpoint(): string
    {
        return $this->_customEndpoint;
    }

    /**
     * Get the API version.
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->_apiVersion;
    }
}
