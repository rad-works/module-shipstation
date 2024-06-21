<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier;

use DmiRud\ShipStation\Model\Api\Data\RateInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject;

class Package extends DataObject implements PackageInterface
{
    public function getName(): ?string
    {
        return $this->getData(self::FIELD_NAME);
    }

    public function getLength(): int
    {
        return $this->getData(self::FIELD_LENGTH) ?: 0;
    }

    public function getWidth(): int
    {
        return $this->getData(self::FIELD_WIDTH) ?: 0;
    }

    public function getHeight(): int
    {
        return $this->getData(self::FIELD_HEIGHT) ?: 0;
    }

    public function getWeight(): int
    {
        return $this->getData(self::FIELD_WEIGHT) ?: 0;
    }

    public function getProductsSkus(): array
    {
        return array_map(fn($product) => $product->getSku(), $this->getProducts());
    }

    public function getRate(): ?RateInterface
    {
        return $this->getData(self::FIELD_RATE);
    }

    /**
     * @return ProductInterface[]
     */
    public function getProducts(): array
    {
        return $this->getData(self::FIELD_PRODUCTS) ?: [];
    }

    public function setName(string $name): PackageInterface
    {
        return $this->setData(self::FIELD_NAME, $name);
    }

    public function setLength(int $length): PackageInterface
    {
        return $this->setData(self::FIELD_LENGTH, $length);
    }

    public function setWidth(int $width): PackageInterface
    {
        return $this->setData(self::FIELD_WIDTH, $width);
    }

    public function setHeight(int $height): PackageInterface
    {
        return $this->setData(self::FIELD_HEIGHT, $height);
    }

    public function setWeight(int $weight): PackageInterface
    {
        return $this->setData(self::FIELD_WEIGHT, $weight);
    }

    public function setRate(?RateInterface $rate): PackageInterface
    {
        return $this->setData(self::FIELD_RATE, $rate);
    }

    /**
     * @param ProductInterface[] $products
     * @return PackageInterface
     */
    public function setProducts(array $products): PackageInterface
    {
        return $this->setData(self::FIELD_PRODUCTS, $products);
    }

    public function setDimensions(array $dimensions): PackageInterface
    {
        //Sort dimensions by size
        rsort($dimensions);
        //Combine dimensions according to the order in constant; the length have the largest value
        return $this->addData(
            array_combine(array_keys(PackageBuilder::XML_PATHS_DIMENSIONS), $dimensions)
        );
    }
}
