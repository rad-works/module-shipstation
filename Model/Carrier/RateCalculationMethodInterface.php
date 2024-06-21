<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier;

use DmiRud\ShipStation\Model\Api\Data\RateInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use DmiRud\ShipStation\Exception\NoServiceFoundForProduct;
use DmiRud\ShipStation\Model\Api\RequestInterface;

interface RateCalculationMethodInterface
{
    /**
     * Prepares ShipStation API request models and its payload
     *
     * @param RateRequest $rateRequest
     * @param DataObject $rawRateRequest
     * @return RequestInterface[]
     * @throws NoServiceFoundForProduct
     */
    public function collectRequests(RateRequest $rateRequest, DataObject $rawRateRequest): array;

    /**
     * Create rates entities from json API response
     *
     * @param string $response
     * @return RateInterface[]
     */
    public function createRatesFromResponse(string $response): array;
}
