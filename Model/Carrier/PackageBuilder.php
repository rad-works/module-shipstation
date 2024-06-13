<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier;

use DmiRud\ShipStation\Exception\NoPackageCreatedForService;
use DmiRud\ShipStation\Model\Api\Data\ServiceInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class PackageBuilder implements PackageBuilderInterface
{
    public const DIMENSION_LENGTH = 'length';
    public const DIMENSION_WIDTH = 'width';
    public const DIMENSION_HEIGHT = 'height';
    private const XML_PATH_DIMENSIONS_LENGTH = 'carriers/shipstation/dimension_length';
    private const XML_PATH_DIMENSIONS_WIDTH = 'carriers/shipstation/dimension_width';
    private const XML_PATH_DIMENSIONS_HEIGHT = 'carriers/shipstation/dimension_height';
    private const XML_PATHS_DIMENSIONS = [
        self::DIMENSION_LENGTH => self::XML_PATH_DIMENSIONS_LENGTH,
        self::DIMENSION_WIDTH => self::XML_PATH_DIMENSIONS_WIDTH,
        self::DIMENSION_HEIGHT => self::XML_PATH_DIMENSIONS_HEIGHT
    ];

    public function __construct(
        private readonly BoxPackerInterface      $boxPacker,
        private readonly PackageInterfaceFactory $packageFactory,
        private readonly ScopeConfigInterface    $scopeConfig
    )
    {
    }

    public function build(ServiceInterface $service, ProductInterface $product): PackageInterface
    {
        $package = $this->packageFactory->create();
        $package->setName($service->getInternalCode());
        $package->setProducts([$product]);
        $package->setWeight((int)$product->getWeight());
        $dimensions = [];
        foreach (self::XML_PATHS_DIMENSIONS as $configPath) {
            $dimensions[] = (int)$product->getData($this->scopeConfig->getValue($configPath));
        }

        //Sort dimensions by size
        rsort($dimensions);
        //Combine dimensions according to the order in constant; the length have the largest value
        $package->addData(array_combine(array_keys(self::XML_PATHS_DIMENSIONS), $dimensions));
        if (!$service->getRestrictions()
            ||
            $package->getLength() >= $service->getRestrictions()->getMaxLength()
            ||
            $package->getWeight() >= $service->getRestrictions()->getMaxWeight()
        ) {
            throw new NoPackageCreatedForService($service);
        }


        return $package;
    }

    public function buildPacked(ServiceInterface $service, array $products): array
    {
        return $this->boxPacker->pack(
            $service->getRestrictions(),
            array_map(fn($product) => $this->build($service, $product), $products)
        );
    }
}