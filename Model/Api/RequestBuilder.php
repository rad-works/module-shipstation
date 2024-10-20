<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Api;

use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use RadWorks\ShipStation\Model\Api\Data\ServiceInterface;
use RadWorks\ShipStation\Model\Carrier\PackageInterface;

class RequestBuilder implements RequestBuilderInterface
{
    public const API_WEIGHT_UNITS_POUNDS = 'pounds';
    public const API_AREA_UNITS_INCHES = 'inches';

    public function __construct(
        private readonly RequestInterfaceFactory $requestFactory,
        private readonly SerializerInterface     $serializer
    ) {
    }

    public function build(PackageInterface $package, ServiceInterface $service, DataObject $rawRateRequest): RequestInterface
    {
        $request = $this->requestFactory->create();
        $payload = [
            'carrierCode' => $service->getCarrierCode(),
            'fromPostalCode' => $rawRateRequest->getOrigPostal(),
            'fromCity' => $rawRateRequest->getOrigCity(),
            'fromState' => $rawRateRequest->getOrigRegionCode(),
            'toPostalCode' => $rawRateRequest->getDestPostal(),
            'toState' => $rawRateRequest->getDestRegionCode(),
            'toCountry' => $rawRateRequest->getDestCountry(),
            'weight' => [
                'units' => self::API_WEIGHT_UNITS_POUNDS,
                'value' => $package->getWeight()
            ],
            'dimensions' => [
                'length' => $package->getLength(),
                'width' => $package->getWidth(),
                'height' => $package->getHeight(),
                'units' => self::API_AREA_UNITS_INCHES
            ]
        ];

        $request->setPayloadSerialized($this->serializer->serialize($payload));
        $request->setPayload($payload);
        $request->setPackage($package);
        $request->setService($service);

        return $request;
    }
}
