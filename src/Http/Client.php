<?php declare(strict_types=1);

namespace PCDB\Http;

use GuzzleHttp\Client as HttpClient;
use Psr\Log\LoggerInterface;
use PCDB\Exceptions\PCDBException;

/**
 * PCDB Client
 *
 * Provides Client methods to interact with Pinecone's vector database API.
 */
class Client
{
    private HttpClient $_httpClient;
    private string $_baseUrl;
    private string $_apiKey;
    private ?LoggerInterface $_logger;

    /**
     * Client constructor.
     *
     * @param Config               $config         Configuration object.
     * @param LoggerInterface|null $logger         Optional logger.
     * @param string|null          $customEndpoint Custom endpoint.
     */
    public function __construct(Config $config, ?LoggerInterface $logger = null, ?string $customEndpoint = null)
    {
        $this->_apiKey = $config->getApiKey();
        $this->_baseUrl = $customEndpoint ?? $config->getEnvironment();
        $this->_httpClient = new HttpClient(
            [
            'base_uri' => $this->_baseUrl,
            'headers'  => [
                'Content-Type'       => 'application/json',
                'Api-Key'            => $this->_apiKey,
                'X-Pinecone-API-Version' => $config->getApiVersion(),
            ],
            ]
        );
        $this->_logger = $logger;
    }

    /**
     * Makes an HTTP request to the PCDB API.
     *
     * @param string                    $method   HTTP method.
     * @param string                    $endpoint API endpoint.
     * @param array<string, mixed>|null $data     Optional data to send.
     *
     * @return array<mixed>
     *
     * @throws PCDBException
     */
    public function request(string $method, string $endpoint, ?array $data = null): array
    {
        $options = [];
        if ($data) {
            $options['json'] = $data;
        }

        try {
            $response = $this->_httpClient->request($method, $endpoint, $options);
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error($e->getMessage());
            }
            throw new PCDBException($e->getMessage());
        }

        $responseBody = $response->getBody()->getContents();

        if (empty($responseBody)) {
            return [];
        }

        $responseData = json_decode($responseBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PCDBException('Invalid JSON response');
        }

        return (array) $responseData;
    }
}
