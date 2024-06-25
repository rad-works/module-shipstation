<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier\BoxPacker;

use DmiRud\ShipStation\Model\Carrier\Package;
use DVDoug\BoxPacker\PackedBox;
use DVDoug\BoxPacker\PackedBoxSorter as BasePackedBoxSorter;

class PackedBoxSorter implements BasePackedBoxSorter
{
    /**
     * Return -1 if $boxA is "best", 1 if $boxB is "best" or 0 if neither is "best".
     */
    public function compare(PackedBox $boxA, PackedBox $boxB): int
    {
        return $this->getPackedBoxLengthWithGirth($boxB) <=> $this->getPackedBoxLengthWithGirth($boxA);
    }

    /**
     * @param PackedBox $box
     * @return int
     */
    public function getPackedBoxLengthWithGirth(PackedBox $box): int
    {
        return Package::calculateLengthWithGirth($box->getUsedLength(), $box->getUsedWidth(), $box->getUsedDepth());
    }
}