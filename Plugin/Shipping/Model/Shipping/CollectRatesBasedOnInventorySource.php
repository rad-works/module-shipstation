<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Plugin\Shipping\Model\Shipping;

use DmiRud\ShipStation\Model\Api\RequestInterface;
use DmiRud\ShipStation\Model\Carrier;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Shipping;
use TheSGroup\ShippingOrigin\Plugin\Shipping\Model\ShippingPlugin;

class CollectRatesBasedOnInventorySource extends ShippingPlugin
{
    /**
     * Update collect carriers
     *
     * @param Shipping $subject
     * @param callable $proceed
     * @param string $carrierCode
     * @param RateRequest $request
     *
     * @return Shipping
     * @throws InputException|LocalizedException|NoSuchEntityException
     * @TODO Factor out MSI rates collection logic from TheSGroup_ShippingOrigin module
     */
    public function aroundCollectCarrierRates(Shipping $subject, callable $proceed, $carrierCode, $request): Shipping
    {
        parent::aroundCollectCarrierRates($subject, $proceed, $carrierCode, $request);
        $carrierRates = $subject->getResult()->getRatesByCarrier($carrierCode);
        if (!($carrierCode === Carrier::CODE && $carrierRates)) {
            return $subject;
        }

        $rates = $subject->getResult()->getAllRates();
        $hasErrors = !!array_filter($carrierRates, fn($rate) => $rate instanceof Error);
        $subject->getResult()->reset();
        foreach ($rates as $rate) {
            if ($rate->getCarrier() === $carrierCode && $this->isFailedServiceRequest($rate, $hasErrors)) {
                continue;
            }

            $subject->getResult()->append($rate);
        }

        return $subject;
    }

    /**
     * Check integrity of rate produced by multiple shipping origin requests
     *
     * @param DataObject $rate
     * @param bool $hasErrors
     * @return bool
     */
    public function isFailedServiceRequest(DataObject $rate, bool $hasErrors): bool
    {
        foreach (Carrier::getRequests(Carrier::RATE_REQUEST_STATUS_FAILED, true) as $serviceCode) {
            if ($serviceCode === $rate->getMethod() || ($hasErrors && !$rate instanceof Error)) {
                return true;
            }
        }

        return false;
    }
}