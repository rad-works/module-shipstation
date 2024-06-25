<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier;

use DmiRud\ShipStation\Model\Carrier\BoxPacker\Item;
use DmiRud\ShipStation\Model\Carrier\BoxPacker\PackedBoxSorter;
use DVDoug\BoxPacker\PackedBox;
use DVDoug\BoxPacker\Packer;
use DVDoug\BoxPacker\Rotation;
use DVDoug\BoxPacker\Test\TestBox;

class BoxPackerFacade implements BoxPackerInterface
{
    public function __construct(private readonly PackageInterfaceFactory $packageFactory)
    {
    }

    /**
     * Repack packages based on service restrictions
     *
     * @param ServiceRestrictionsInterface $serviceRestrictions
     * @param PackageInterface[] $packages
     * @return PackageInterface[]
     */
    public function pack(ServiceRestrictionsInterface $serviceRestrictions, array $packages): array
    {
        $packer = new Packer();
        $packer->setPackedBoxSorter(new PackedBoxSorter());
        [$length, $width, $height] = $this->prepareBoxDimensions($packages, $serviceRestrictions->getMaxLengthWithGirth());
        $packer->addBox(
            new TestBox(
                reference: $serviceRestrictions->getService()->getInternalCode(),
                outerWidth: $width,
                outerLength: $length,
                outerDepth: $height,
                emptyWeight: 0,
                innerWidth: $width,
                innerLength: $length,
                innerDepth: $height,
                maxWeight: $serviceRestrictions->getMaxWeight()
            )
        );
        foreach ($packages as $package) {
            $product = current($package->getProducts());
            $packer->addItem(new Item($package, $product, Rotation::BestFit));
        }

        $packages = [];
        $packedBoxes = $packer->pack();
        /** @var PackedBox $packedBox */
        foreach ($packedBoxes as $packedBox) {
            $package = $this->packageFactory->create();
            $package->setName($serviceRestrictions->getService()->getInternalCode());
            $package->setProducts(
                array_map(fn($item) => $item->getProduct(), $packedBox->getItems()->asItemArray())
            );
            $package->setWeight((int)$packedBox->getWeight());
            $package->setDimensions([
                $packedBox->getUsedLength(),
                $packedBox->getUsedWidth(),
                $packedBox->getUsedDepth()
            ]);
            $packages[] = $package;
        }

        return $packages;
    }

    /**
     * Prepare rough estimate of a box/container dimensions
     *
     * @param array $packages
     * @param int $maxLengthWithGirth
     * @return array
     */
    private function prepareBoxDimensions(array $packages, int $maxLengthWithGirth): array
    {
        $length = $width = $height = 0;
        foreach ($packages as $package) {
            if ($length < $package->getLength()) {
                $length = $package->getLength();
            }

            if ($width < $package->getWidth()) {
                $width = $package->getWidth();
            }

            $height += $package->getHeight();
        }

        while (Package::calculateLengthWithGirth($length, $width, $height) >= $maxLengthWithGirth) {
            $height -= 1;
        }

        return [$length, $width, $height];
    }
}
