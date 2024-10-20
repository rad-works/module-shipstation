<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Carrier\Rate;

use RadWorks\ShipStation\Model\Carrier\PackageBuilderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Shipping\Model\Rate\PackageResultFactory;
use RadWorks\ShipStation\Model\Api\Data\Rate;
use RadWorks\ShipStation\Model\Api\Data\RateInterface;
use RadWorks\ShipStation\Model\Api\Data\RateInterfaceFactory;
use RadWorks\ShipStation\Model\Api\DataProviderInterface;
use RadWorks\ShipStation\Model\Api\RequestBuilderInterface;
use RadWorks\ShipStation\Model\Carrier\RateCalculationMethodInterface;

abstract class CalculationMethodAbstract implements RateCalculationMethodInterface
{
    public function __construct(
        protected readonly DataProviderInterface      $dataProvider,
        protected readonly Escaper                    $escaper,
        protected readonly RateInterfaceFactory       $rateFactory,
        protected readonly PackageBuilderInterface    $packageBuilder,
        protected readonly PackageResultFactory       $packageResultFactory,
        protected readonly ProductRepositoryInterface $productRepository,
        protected readonly RequestBuilderInterface    $requestBuilder,
        protected readonly Json                       $serializer,
        protected readonly ScopeConfigInterface       $scopeConfig
    ) {
    }

    /**
     * @param string $response
     * @return RateInterface[]
     */
    public function createRatesFromResponse(string $response): array
    {
        $rates = [];
        foreach ($this->serializer->unserialize($response) as $data) {
            /** @var Rate $rate */
            $rate = $this->rateFactory->create()->addData($data);
            $rate->setServiceName($this->escaper->escapeHtml($rate->getServiceName()));
            $rate->setServiceCode($this->escaper->escapeHtml($rate->getServiceCode()));
            if ($service = $this->dataProvider->getServiceByCode($rate->getServiceCode())) {
                $rates[$rate->getServiceCode()] = $rate->setService($service);
            }
        }

        return $rates;
    }
}
