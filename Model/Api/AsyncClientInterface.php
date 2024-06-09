<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Throwable;

interface AsyncClientInterface
{
    /**
     * Send async ShipStation API request
     *
     * @param string $url
     * @param string $method
     * @param string $body
     * @return HttpResponseDeferredInterface
     */
    public function sendRequest(string $url, string $method = 'POST', string $body = ''): HttpResponseDeferredInterface;

    /**
     * Get cached API response or make request
     *
     * @param string $url
     * @param string|null $validationKey
     * @return array
     * @throws LocalizedException|Throwable
     */
    public function getCachedResponse(string $url, string $validationKey = null): array;
}
