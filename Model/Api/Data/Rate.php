<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Api\Data;

use Magento\Framework\DataObject;

class Rate extends DataObject implements RateInterface
{
    private const DEFAULT_RATE_ADJUSTMENT_MODIFIER = 1.0;

    public function getServiceName(): string
    {
        return $this->getData(self::FIELD_SERVICE_NAME);
    }

    public function getServiceCode(): string
    {
        return $this->getData(self::FIELD_SERVICE_CODE);
    }

    public function getShipmentCost(): float
    {
        return $this->getData(self::FIELD_SHIPMENT_COST);
    }

    public function getOtherCost(): float
    {
        return $this->getData(self::FIELD_OTHER_COST);
    }

    public function getCostAdjustmentModifier(): float
    {
        if ($rateAdjustment = $this->getService()->getRestrictions()?->getRateAdjustment()) {
            return $rateAdjustment;
        }

        return self::DEFAULT_RATE_ADJUSTMENT_MODIFIER;
    }

    public function getTotalCost(): float
    {
        return $this->getCostAdjustmentModifier() * ($this->getShipmentCost() + $this->getOtherCost());
    }

    public function getService(): ServiceInterface
    {
        return $this->getData(self::FIELD_CUSTOM_SERVICE);
    }

    public function setServiceName(string $serviceName): RateInterface
    {
        return $this->setData(self::FIELD_SERVICE_NAME, $serviceName);
    }

    public function setServiceCode(string $serviceCode): RateInterface
    {
        return $this->setData(self::FIELD_SERVICE_CODE, $serviceCode);
    }

    public function setShipmentCost(float $shipmentCost): RateInterface
    {
        return $this->setData(self::FIELD_SHIPMENT_COST, $shipmentCost);
    }

    public function setOtherCost(float $otherCost): RateInterface
    {
        return $this->setData(self::FIELD_OTHER_COST, $otherCost);
    }

    public function setService(ServiceInterface $service): RateInterface
    {
        return $this->setData(self::FIELD_CUSTOM_SERVICE, $service);
    }
}
