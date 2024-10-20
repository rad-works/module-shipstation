<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Carrier;

use RadWorks\ShipStation\Model\Api\Data\RateInterface;
use Magento\Catalog\Api\Data\ProductInterface;

interface PackageInterface
{
    public const FIELD_NAME = 'name';
    public const FIELD_LENGTH = 'length';
    public const FIELD_WIDTH = 'width';
    public const FIELD_HEIGHT = 'height';
    public const FIELD_WEIGHT = 'weight';
    public const FIELD_RATE = 'rate';
    public const FIELD_PRODUCTS = 'products';

    public function getName(): ?string;

    public function getLength(): int;

    public function getWidth(): int;

    public function getHeight(): int;

    public function getWeight(): int;

    /**
     * Get calculated value of a girth with length
     *
     * @return int
     */
    public function getLengthWithGirth(): int;

    /**
     * @return RateInterface|null
     */
    public function getRate(): ?RateInterface;

    /**
     * @return ProductInterface[]
     */
    public function getProducts(): array;

    /**
     * @return array
     */
    public function getProductsSkus(): array;

    public function setName(string $name): PackageInterface;

    public function setLength(int $length): PackageInterface;

    public function setWidth(int $width): PackageInterface;

    public function setHeight(int $height): PackageInterface;

    public function setWeight(int $weight): PackageInterface;

    /**
     * @param RateInterface|null $rate
     * @return PackageInterface
     */
    public function setRate(RateInterface|null $rate): PackageInterface;

    /**
     * @param ProductInterface[] $products
     * @return PackageInterface
     */
    public function setProducts(array $products): PackageInterface;

    /**
     * Set dimensions from array
     *
     * @param array $dimensions
     * @return PackageInterface
     */
    public function setDimensions(array $dimensions): PackageInterface;
}
