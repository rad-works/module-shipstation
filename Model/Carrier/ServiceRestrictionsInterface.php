<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier;

use DmiRud\ShipStation\Model\Api\Data\ServiceInterface;

interface ServiceRestrictionsInterface
{
    public const FIELD_MAX_LENGTH = 'max_length';
    public const FIELD_MAX_LENGTH_WITH_GIRTH = 'max_length_with_girth';
    public const FIELD_MAX_WEIGHT = 'max_weight';
    public const FIELD_DIMENSIONAL_WEIGHT = 'dimensional_weight';
    public const FIELD_RATE_ADJUSTMENT = 'rate_adjustment';
    public const FIELD_SUBTOTAL_ADJUSTMENT = 'subtotal_adjustment';
    public const FIELD_SERVICE = 'service';

    public function getMaxLength(): int;

    public function getMaxLengthWithGirth(): int;

    public function getMaxWeight(): int;

    public function getDimensionalWeight(): int;

    public function getRateAdjustment(): float;

    public function getSubtotalAdjustment(): float;

    public function getService(): ServiceInterface;

    public function setMaxLength(int $maxLength): self;

    public function setMaxLengthWithGirth(int $maxLength): self;

    public function setMaxWeight(int $maxWeight): self;

    public function setDimensionalWeight(int $dimensionalWeight): self;

    public function setRateAdjustment(float $rateAdjustment): self;

    public function setSubtotalAdjustment(float $subtotalAdjustment): self;

    public function setService(ServiceInterface $service): self;
}
