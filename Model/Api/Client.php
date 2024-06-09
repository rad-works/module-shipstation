<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Api;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClientInterface as HttpAsyncClientInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Throwable;
use DmiRud\ShipStation\Model\Cache\Type\ApiResponse;

class Client implements AsyncClientInterface
{
    public const API_URL = 'https://ssapi.shipstation.com/';
    public const API_RATES_URL = self::API_URL . 'shipments/getrates';
    public const API_CARRIERS_URL = self::API_URL . 'carriers';
    public const API_SERVICES_URL = self::API_URL . 'carriers/listservices';
    private const XML_PATH_API_KEY = 'carriers/shipstation/ss_api_key';
    private const XML_PATH_API_SECRET = 'carriers/shipstation/ss_api_secret';
    private const CACHE_LIFETIME = 2.628e+6;

    public function __construct(
        private readonly HttpAsyncClientInterface $asyncClient,
        private readonly CacheInterface           $cache,
        private readonly Json                     $json,
        private readonly ScopeConfigInterface     $scopeConfig
    ) {
    }

    /**
     * Send async ShipStation API request
     *
     * @param string $url
     * @param string $method
     * @param string $body
     * @return HttpResponseDeferredInterface
     */
    public function sendRequest(string $url, string $method = 'POST', string $body = ''): HttpResponseDeferredInterface
    {
        $basic = $this->scopeConfig->getValue(self::XML_PATH_API_KEY)
            . ':'
            . $this->scopeConfig->getValue(self::XML_PATH_API_SECRET);
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($basic)
        ];

        return $this->asyncClient->request(new Request($url, $method, $headers, $body));
    }

    /**
     * Get cached API response or make request
     *
     * @param string $url
     * @param string|null $validationKey
     * @return array
     * @throws LocalizedException|Throwable
     */
    public function getCachedResponse(string $url, string $validationKey = null): array
    {
        $cacheId = ApiResponse::TYPE_IDENTIFIER . '_'  . md5($url);
        if (!($responseBody = $this->cache->load($cacheId))) {
            $responseBody = $this->sendRequest($url, Request::METHOD_GET)->get()?->getBody();
            $response = $this->json->unserialize($responseBody);
            if (!$this->isValidResponseData($response, $validationKey)) {
                throw new LocalizedException(__('Invalid ShipStation API response data.'));
            }

            $this->cache->save($responseBody, $cacheId, lifeTime: self::CACHE_LIFETIME);
        }

        return  $this->convertResponseToSnakeCase($this->json->unserialize($responseBody));
    }

    /**
     * Validate response data
     *
     * @param mixed $response
     * @param string|null $validationKey
     * @return bool
     */
    public function isValidResponseData(mixed $response, ?string $validationKey): bool
    {
        return is_array($response) && (!$validationKey || array_column($response, $validationKey));
    }

    /**
     * Convert response entities keys to the snake case
     *
     * @param array $response
     * @return array
     */
    private function convertResponseToSnakeCase(array $response): array
    {
        $result = [];
        foreach ($response as $index => $item) {
            foreach ($item as $key => $value) {
                $result[$index][SimpleDataObjectConverter::camelCaseToSnakeCase($key)] = $value;
            }
        }

        return $result;
    }
}
