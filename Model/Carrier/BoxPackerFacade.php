<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier;

use DmiRud\ShipStation\Model\Carrier\BoxPacker\Item;
use DVDoug\BoxPacker\PackedBox;
use DVDoug\BoxPacker\Packer;
use DVDoug\BoxPacker\Rotation;
use DVDoug\BoxPacker\Test\TestBox;

class BoxPackerFacade implements BoxPackerInterface
{
    public function __construct(private readonly PackageInterfaceFactory $packageFactory)
    {
    }

    public function pack(ServiceRestrictionsInterface $serviceRestrictions, array $packages): array
    {
        $packer = new Packer();
        $packer->addBox(
            new TestBox(
                reference: $serviceRestrictions->getService()->getInternalCode(),
                outerWidth: $serviceRestrictions->getMaxLength(),
                outerLength: $serviceRestrictions->getMaxLength(),
                outerDepth: $serviceRestrictions->getMaxLength(),
                emptyWeight: 0,
                innerWidth: $serviceRestrictions->getMaxLength(),
                innerLength: $serviceRestrictions->getMaxLength(),
                innerDepth: $serviceRestrictions->getMaxLength(),
                maxWeight: $serviceRestrictions->getMaxWeight()
            )
        );
        foreach ($packages as $package) {
            $product = current($package->getProducts());
            $packer->addItem(new Item(
                description: $product->getSku(),
                width: $package->getWidth(),
                length: $package->getLength(),
                depth: $package->getHeight(),
                weight: $package->getWeight(),
                allowedRotation: Rotation::BestFit,
                product: $product
            ));
        }

        $packages = [];
        /** @var PackedBox $packedBox */
        foreach ($packer->pack() as $packedBox) {
            /** @var PackageInterface $package */
            $package = $this->packageFactory->create();
            $package->setName($serviceRestrictions->getService()->getInternalCode());
            $package->setProducts(
                array_map(fn($item) => $item->getProduct(), $packedBox->getItems()->asItemArray())
            );
            $package->setWeight((int)$packedBox->getWeight());
            $package->getLength($packedBox->getUsedLength());
            $package->getWidth($packedBox->getUsedWidth());
            $package->getHeight($packedBox->getUsedDepth());
        }

        return $packages;
    }
}