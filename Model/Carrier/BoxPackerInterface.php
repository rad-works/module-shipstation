<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Carrier;

use RadWorks\ShipStation\Model\Api\Data\ServiceInterface;

interface BoxPackerInterface
{
    /**
     * Repack packages based on service restrictions
     *
     * @param ServiceRestrictionsInterface $serviceRestrictions
     * @param PackageInterface[] $packages
     * @return PackageInterface[]
     */
    public function pack(ServiceRestrictionsInterface $serviceRestrictions, array $packages): array;
}
