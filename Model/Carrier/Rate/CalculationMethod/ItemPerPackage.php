<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier\Rate\CalculationMethod;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\Item;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\PackageResult;
use Magento\Shipping\Model\Rate\Result as RateResult;
use DmiRud\ShipStation\Exception\NoServiceFoundForPackage;
use DmiRud\ShipStation\Model\Api\RequestInterface;
use DmiRud\ShipStation\Model\Carrier;
use DmiRud\ShipStation\Model\Carrier\Rate\CalculationMethodAbstract;

class ItemPerPackage extends CalculationMethodAbstract
{
    public const METHOD_CODE = 'item_per_package';

    /**
     * Prepares ShipStation API request models and its payload
     *
     * @param RateRequest $rateRequest
     * @param DataObject $rawRateRequest
     * @return RequestInterface[]
     * @throws NoServiceFoundForPackage|NoSuchEntityException
     * @TODO add integrity validation logic for a case in which products have different carriers available
     */
    public function collectRequests(RateRequest $rateRequest, DataObject $rawRateRequest): array
    {
        $requests = [];
        $countryCode = $rawRateRequest->getDestCountry();
        /** @var Item $item */
        foreach ($rateRequest->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            $product = $this->productRepository->get($item->getSku());
            $qty = $item->getQty();
            $itemRequests = [];
            while ($qty--) {
                $package = $this->createPackageForProduct($product);
                foreach ($this->dataProvider->getServicesByDestCountryCode($countryCode) as $service) {
                    if (
                        $service->getRestrictions()
                        && (
                        $package->getLength() >= $service->getRestrictions()->getMaxLength()
                        ||
                        $package->getWeight() >= $service->getRestrictions()->getMaxWeight()
                    )) {
                        continue;
                    }

                    $request = $this->requestBuilder->build($package, $service, $rawRateRequest);
                    $itemRequests[] = $request;
                }
            }

            $requests = array_merge($requests, $itemRequests);
            if (!$itemRequests) {
                throw new NoServiceFoundForPackage;
            }
        }

        return $requests;
    }

    /**
     * Get rate models from API response data
     *
     * @param Carrier $carrier
     * @param array $responses
     * @return RateResult
     */
    public function getRateResult(Carrier $carrier, array $responses): RateResult
    {
        /** @var PackageResult $packageResult */
        $packageResult = $this->packageResultFactory->create();
        foreach ($responses as $result) {
            $rateResult = $this->rateResultFactory->create();
            [$request, $response] = $result;
            foreach ($this->getRatesFromResponse($response) as $rate) {
                if ($rate->getService()->getCode() !== $request->getService()->getCode()) {
                    continue;
                }

                $cost = ($rate->getShipmentCost() + $rate->getOtherCost()) * $rate->getCostAdjustmentModifier();
                $rateMethod = $this->rateResultMethodFactory->create();
                $rateMethod->setCarrier($carrier->getCarrierCode());
                $rateMethod->setCarrierTitle($carrier->getConfigData('title'));
                $rateMethod->setMethod($this->escaper->escapeHtml($rate->getServiceCode()));
                $rateMethod->setMethodTitle($this->escaper->escapeHtml($rate->getServiceName()));
                $rateMethod->setCost($cost);
                $rateMethod->setPrice($cost);
                $rateResult->append($rateMethod);
            }

            $packageResult->appendPackageResult($rateResult, 1);
        }

        return $packageResult;
    }
}
