<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Carrier;

use RadWorks\ShipStation\Exception\NoPackageCreatedForService;
use RadWorks\ShipStation\Model\Api\Data\ServiceInterface;
use Magento\Catalog\Api\Data\ProductInterface;

interface PackageBuilderInterface
{
    /**
     * Get package for single products
     *
     * @param ServiceInterface $service
     * @param ProductInterface $product
     * @return PackageInterface
     * @throws NoPackageCreatedForService
     */
    public function build(ServiceInterface $service, ProductInterface $product): PackageInterface;

    /**
     * Build packages from multiple products
     *
     * @param ProductInterface[] $products
     * @return PackageInterface[]
     * @throws NoPackageCreatedForService
     */
    public function buildPacked(ServiceInterface $service, array $products): array;
}
