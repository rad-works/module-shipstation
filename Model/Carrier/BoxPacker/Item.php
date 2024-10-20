<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Carrier\BoxPacker;

use RadWorks\ShipStation\Model\Carrier\PackageInterface;
use DVDoug\BoxPacker\Rotation;
use DVDoug\BoxPacker\Test\TestItem;
use Magento\Catalog\Api\Data\ProductInterface;

class Item extends TestItem
{
    private PackageInterface $package;
    private ProductInterface $product;

    public function __construct(PackageInterface $package, ProductInterface $product)
    {
        $this->package = $package;
        $this->product = $product;
        parent::__construct(
            $this->product->getSku(),
            $this->package->getWidth(),
            $this->package->getLength(),
            $this->package->getHeight(),
            $this->package->getWeight(),
            Rotation::KeepFlat
        );
    }

    /**
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface
    {
        return $this->product;
    }
}
