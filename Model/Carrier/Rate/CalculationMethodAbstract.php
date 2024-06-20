<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier\Rate;

use DmiRud\ShipStation\Model\Carrier;
use DmiRud\ShipStation\Model\Carrier\PackageBuilderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\PackageResult;
use Magento\Shipping\Model\Rate\PackageResultFactory;
use Magento\Shipping\Model\Rate\Result as RateResult;
use Magento\Shipping\Model\Rate\ResultFactory;
use DmiRud\ShipStation\Model\Api\Data\Rate;
use DmiRud\ShipStation\Model\Api\Data\RateInterface;
use DmiRud\ShipStation\Model\Api\Data\RateInterfaceFactory;
use DmiRud\ShipStation\Model\Api\DataProviderInterface;
use DmiRud\ShipStation\Model\Api\RequestBuilderInterface;
use DmiRud\ShipStation\Model\Carrier\RateCalculationMethodInterface;

abstract class CalculationMethodAbstract implements RateCalculationMethodInterface
{

    public function __construct(
        protected readonly DataProviderInterface      $dataProvider,
        protected readonly Escaper                    $escaper,
        protected readonly RateInterfaceFactory       $rateFactory,
        protected readonly ResultFactory              $rateResultFactory,
        protected readonly MethodFactory              $rateResultMethodFactory,
        protected readonly PackageBuilderInterface    $packageBuilder,
        protected readonly PackageResultFactory       $packageResultFactory,
        protected readonly ProductRepositoryInterface $productRepository,
        protected readonly RequestBuilderInterface    $requestBuilder,
        protected readonly Json                       $serializer,
        protected readonly ScopeConfigInterface       $scopeConfig
    ) {
    }


    /**
     * Get rate models from API response data
     *
     * @param Carrier $carrier
     * @param array $responses
     * @return RateResult
     */
    public function getRateResult(Carrier $carrier, array $responses): RateResult
    {
        /** @var PackageResult $packageResult */
        $packageResult = $this->packageResultFactory->create();
        foreach ($responses as $result) {
            $rateResult = $this->rateResultFactory->create();
            [$request, $response] = $result;
            foreach ($this->getRatesFromResponse($response) as $rate) {
                if ($rate->getService()->getCode() !== $request->getService()->getCode()) {
                    continue;
                }

                $cost = ($rate->getShipmentCost() + $rate->getOtherCost()) * $rate->getCostAdjustmentModifier();
                $rateMethod = $this->rateResultMethodFactory->create();
                $rateMethod->setCarrier($carrier->getCarrierCode());
                $rateMethod->setCarrierTitle($carrier->getConfigData('title'));
                $rateMethod->setMethod($this->escaper->escapeHtml($rate->getServiceCode()));
                $rateMethod->setMethodTitle($this->escaper->escapeHtml($rate->getServiceName()));
                $rateMethod->setCost($cost);
                $rateMethod->setPrice($cost);
                $rateResult->append($rateMethod);
            }

            $packageResult->appendPackageResult($rateResult, 1);
        }

        return $packageResult;
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
