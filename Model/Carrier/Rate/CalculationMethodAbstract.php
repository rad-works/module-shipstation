<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier\Rate;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\PackageResultFactory;
use Magento\Shipping\Model\Rate\ResultFactory;
use DmiRud\ShipStation\Model\Api\Data\Rate;
use DmiRud\ShipStation\Model\Api\Data\RateInterface;
use DmiRud\ShipStation\Model\Api\Data\RateInterfaceFactory;
use DmiRud\ShipStation\Model\Api\DataProviderInterface;
use DmiRud\ShipStation\Model\Api\RequestBuilderInterface;
use DmiRud\ShipStation\Model\Carrier\PackageInterface;
use DmiRud\ShipStation\Model\Carrier\PackageInterfaceFactory;
use DmiRud\ShipStation\Model\Carrier\RateCalculationMethodInterface;

abstract class CalculationMethodAbstract implements RateCalculationMethodInterface
{
    private const XML_PATH_DIMENSIONS_LENGTH = 'carriers/shipstation/dimension_length';
    private const XML_PATH_DIMENSIONS_WIDTH = 'carriers/shipstation/dimension_width';
    private const XML_PATH_DIMENSIONS_HEIGHT = 'carriers/shipstation/dimension_height';
    private const XML_PATHS_DIMENSIONS = [
        'length' => self::XML_PATH_DIMENSIONS_LENGTH,
        'width' => self::XML_PATH_DIMENSIONS_WIDTH,
        'height' => self::XML_PATH_DIMENSIONS_HEIGHT
    ];

    public function __construct(
        protected readonly DataProviderInterface      $dataProvider,
        protected readonly Escaper                    $escaper,
        protected readonly RateInterfaceFactory       $rateFactory,
        protected readonly ResultFactory              $rateResultFactory,
        protected readonly MethodFactory              $rateResultMethodFactory,
        protected readonly PackageInterfaceFactory    $packageFactory,
        protected readonly PackageResultFactory       $packageResultFactory,
        protected readonly ProductRepositoryInterface $productRepository,
        protected readonly RequestBuilderInterface    $requestBuilder,
        protected readonly Json                       $serializer,
        protected readonly ScopeConfigInterface       $scopeConfig
    )
    {
    }

    /**
     * @param ProductInterface $product
     * @param PackageInterface|null $package
     * @return PackageInterface
     * @throws NoSuchEntityException
     * @TODO add logic for "Bin packing problem"
     */
    protected function createPackageForProduct(ProductInterface $product, PackageInterface $package = null): PackageInterface
    {
        $package = $this->packageFactory->create();
        $package->setProducts([$product]);
        $package->setWeight((int)$product->getWeight());
        $product = $this->productRepository->get($product->getSku());
        $dimensions = [];
        foreach (self::XML_PATHS_DIMENSIONS as $configPath) {
            $dimensions[] = (int) $product->getData($this->scopeConfig->getValue($configPath));
        }
        //Sort dimensions by size
        rsort($dimensions);
        //Combine dimensions according to the order in constant; the length have the largest number
        $package->addData(array_combine(array_keys(self::XML_PATHS_DIMENSIONS), $dimensions));

        return $package;
    }

    /**
     * @param string $response
     * @return RateInterface[]
     */
    public function getRatesFromResponse(string $response): array
    {
        $rates = [];
        foreach ($this->serializer->unserialize($response) as $data) {
            /** @var Rate $rate */
            $rate = $this->rateFactory->create()->addData($data);
            if ($service = $this->dataProvider->getServiceByCode($rate->getServiceCode())) {
                $rates[] = $rate->setService($service);
            }
        }

        return $rates;
    }
}
