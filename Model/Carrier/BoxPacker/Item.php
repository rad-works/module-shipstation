<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier\BoxPacker;

use DmiRud\ShipStation\Model\Carrier\PackageInterface;
use DVDoug\BoxPacker\Test\TestItem;
use Magento\Catalog\Api\Data\ProductInterface;

class Item extends TestItem
{
    private PackageInterface $package;
    private ProductInterface $product;

    public function __construct(PackageInterface $package, ProductInterface $product, int $allowedRotation)
    {
        $this->package = $package;
        $this->product = $product;
        parent::__construct(
            $this->product->getSku(),
            $this->package->getWidth(),
            $this->package->getLength(),
            $this->package->getHeight(),
            $this->package->getWeight(),
            $allowedRotation
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
