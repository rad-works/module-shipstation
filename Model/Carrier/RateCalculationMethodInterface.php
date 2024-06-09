<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier;

use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use DmiRud\ShipStation\Exception\NoServiceFoundForPackage;
use DmiRud\ShipStation\Model\Api\RequestInterface;
use DmiRud\ShipStation\Model\Carrier;

interface RateCalculationMethodInterface
{
    /**
     * Prepares ShipStation API request models and its payload
     *
     * @param RateRequest $rateRequest
     * @param DataObject $rawRateRequest
     * @return RequestInterface[]
     * @throws NoServiceFoundForPackage
     */
    public function collectRequests(RateRequest $rateRequest, DataObject $rawRateRequest): array;


    /**
     * Get rate models from API response data
     *
     * @param Carrier $carrier
     * @param array $responses
     * @return Result
     */
    public function getRateResult(Carrier $carrier, array $responses): Result;
}
