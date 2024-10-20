<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Api\Data;

interface RateInterface extends EntityInterface
{
    public const FIELD_SERVICE_NAME = 'serviceName';
    public const FIELD_SERVICE_CODE = 'serviceCode';
    public const FIELD_SHIPMENT_COST = 'shipmentCost';
    public const FIELD_OTHER_COST = 'otherCost';
    public const FIELD_CUSTOM_SERVICE = 'service';

    /**
     * Get service name
     *
     * @return string
     */
    public function getServiceName(): string;

    /**
     * Get service code
     *
     * @return string
     */
    public function getServiceCode(): string;

    /**
     * Get cost
     *
     * @return float
     */
    public function getShipmentCost(): float;

    /**
     * Get additional cost
     *
     * @return float
     */
    public function getOtherCost(): float;

    /**
     * Get cost adjustment modifier
     *
     * @return float
     */
    public function getCostAdjustmentModifier(): float;

    /**
     * Get total calculated cost of the rate
     *
     * @return float
     */
    public function getTotalCost(): float;

    /**
     * Get rate related service model
     *
     * @return ServiceInterface
     */
    public function getService(): ServiceInterface;

    /**
     * Set service name
     *
     * @param string $serviceName
     * @return RateInterface
     */
    public function setServiceName(string $serviceName): RateInterface;

    /**
     * Set service code
     *
     * @param string $serviceCode
     * @return RateInterface
     */
    public function setServiceCode(string $serviceCode): RateInterface;

    /**
     * Set cost
     *
     * @param float $shipmentCost
     * @return RateInterface
     */
    public function setShipmentCost(float $shipmentCost): RateInterface;

    /**
     * Set other cost
     *
     * @param float $otherCost
     * @return RateInterface
     */
    public function setOtherCost(float $otherCost): RateInterface;

    /**
     * Set rate related service model
     *
     * @param ServiceInterface $service
     * @return RateInterface
     */
    public function setService(ServiceInterface $service): RateInterface;
}
