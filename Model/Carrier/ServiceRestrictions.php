<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Carrier;

use Magento\Framework\DataObject;
use RadWorks\ShipStation\Model\Api\Data\ServiceInterface;

class ServiceRestrictions extends DataObject implements ServiceRestrictionsInterface
{
    public function getMaxLength(): int
    {
        return $this->getData(self::FIELD_MAX_LENGTH);
    }

    public function getMaxLengthWithGirth(): int
    {
        return $this->getData(self::FIELD_MAX_LENGTH_WITH_GIRTH);
    }

    public function getMaxWeight(): int
    {
        return $this->getData(self::FIELD_MAX_WEIGHT);
    }

    public function getDimensionalWeight(): int
    {
        return $this->getData(self::FIELD_DIMENSIONAL_WEIGHT);
    }

    public function getRateAdjustment(): float
    {
        return $this->getData(self::FIELD_RATE_ADJUSTMENT);
    }

    public function getSubtotalAdjustment(): float
    {
        return $this->getData(self::FIELD_SUBTOTAL_ADJUSTMENT);
    }

    public function getService(): ServiceInterface
    {
        return $this->getData(self::FIELD_SERVICE);
    }

    public function setMaxLength(int $maxLength): ServiceRestrictionsInterface
    {
        return $this->setData(self::FIELD_MAX_LENGTH, $maxLength);
    }

    public function setMaxLengthWithGirth(int $maxLength): ServiceRestrictionsInterface
    {
        return $this->setData(self::FIELD_MAX_LENGTH_WITH_GIRTH, $maxLength);
    }

    public function setMaxWeight(int $maxWeight): ServiceRestrictionsInterface
    {
        return $this->setData(self::FIELD_MAX_WEIGHT, $maxWeight);
    }

    public function setDimensionalWeight(int $dimensionalWeight): ServiceRestrictionsInterface
    {
        return $this->setData(self::FIELD_DIMENSIONAL_WEIGHT, $dimensionalWeight);
    }

    public function setRateAdjustment(float $rateAdjustment): ServiceRestrictionsInterface
    {
        return $this->setData(self::FIELD_RATE_ADJUSTMENT, $rateAdjustment);
    }

    public function setSubtotalAdjustment(float $subtotalAdjustment): ServiceRestrictionsInterface
    {
        return $this->setData(self::FIELD_SUBTOTAL_ADJUSTMENT, $subtotalAdjustment);
    }

    public function setService(ServiceInterface $service): ServiceRestrictionsInterface
    {
        return $this->setData(self::FIELD_SERVICE, $service);
    }
}
