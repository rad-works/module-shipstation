<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier\BoxPacker;

use DVDoug\BoxPacker\Test\TestItem;
use Magento\Catalog\Api\Data\ProductInterface;

class Item extends TestItem
{
    public function __construct(
        string           $description,
        int              $width,
        int              $length,
        int              $depth,
        int              $weight,
        int              $allowedRotation,
        ProductInterface $product
    )
    {
        $this->product = $product;
        parent::__construct($description, $width, $length, $depth, $weight, $allowedRotation);
    }

    /**
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface
    {
        return $this->product;
    }
}