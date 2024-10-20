<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Api;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Throwable;
use RadWorks\ShipStation\Model\Api\Data\CarrierInterface;
use RadWorks\ShipStation\Model\Api\Data\CarrierInterfaceFactory;
use RadWorks\ShipStation\Model\Api\Data\ServiceInterface;
use RadWorks\ShipStation\Model\Api\Data\ServiceInterfaceFactory;

class DataProvider implements DataProviderInterface
{
    private const XML_PATH_ACTIVE_SERVICES = 'carriers/shipstation/active_services';

    /**
     * @var CarrierInterface[]|null
     */
    private ?array $carriers = null;

    /**
     * @var ServiceInterface[]|null
     */
    private ?array $services = null;

    public function __construct(
        private readonly AsyncClientInterface    $client,
        private readonly CarrierInterfaceFactory $carrierFactory,
        private readonly ServiceInterfaceFactory $serviceFactory,
        private readonly ScopeConfigInterface    $scopeConfig,
        private readonly DataObjectHelper        $dataObjectHelper
    ) {
    }

    /**
     * Get set of active services
     *
     * @return CarrierInterface[]
     * @throws LocalizedException|Throwable
     */
    public function getActiveCarriers(): array
    {
        $carrierCodes = array_keys($this->getAllCarriers());
        $carriers = [];
        foreach ($this->getActiveServices() as $service) {
            if (!in_array($service->getCarrierCode(), $carrierCodes, true)) {
                continue;
            }

            $carriers[$service->getCarrierCode()] = $this->getAllCarriers()[$service->getCarrierCode()];
        }

        return $carriers;
    }

    /**
     * Get set of active services
     *
     * @return ServiceInterface[]
     * @throws LocalizedException|Throwable
     */
    public function getActiveServices(): array
    {
        $services = [];
        if (!$config = $this->scopeConfig->getValue(self::XML_PATH_ACTIVE_SERVICES)) {
            return [];
        }

        $codes = explode(',', $config);
        foreach ($this->getAllServices() as $internalCode => $service) {
            if (!in_array($internalCode, $codes, true)) {
                continue;
            }

            $services[$internalCode] = $service;
        }

        return $services;
    }


    /**
     * Get available carriers
     *
     * @return CarrierInterface[]
     * @throws LocalizedException|Throwable
     */
    public function getAllCarriers(): array
    {
        if ($this->carriers !== null) {
            return $this->carriers;
        }

        $response = $this->client->getCachedResponse(Client::API_CARRIERS_URL, CarrierInterface::FIELD_CODE);
        foreach ($response as $data) {
            /** @var CarrierInterface $carrier */
            $carrier = $this->carrierFactory->create();
            $this->dataObjectHelper->populateWithArray($carrier, $data, CarrierInterface::class);
            $this->carriers[$carrier->getCode()] = $carrier;
        }

        return $this->carriers;
    }

    /**
     * Get available carriers
     *
     * @return ServiceInterface[]
     * @throws LocalizedException|Throwable
     */
    public function getAllServices(): array
    {
        if ($this->services !== null) {
            return $this->services;
        }

        foreach ($this->getAllCarriers() as $carrier) {
            $url = Client::API_SERVICES_URL . '?' . ServiceInterface::FIELD_CARRIER_CODE . '=' . $carrier->getCode();
            foreach ($this->client->getCachedResponse($url, ServiceInterface::FIELD_CODE) as $data) {
                /** @var ServiceInterface $service */
                $service = $this->serviceFactory->create();
                $this->dataObjectHelper->populateWithArray($service, $data, ServiceInterface::class);
                $this->services[$service->getInternalCode()] = $service;
            }
        }

        return $this->services;
    }

    /**
     * Get services by country code
     *
     * @param string $countryCode
     * @return ServiceInterface[]
     * @throws LocalizedException|Throwable
     */
    public function getServicesByDestCountryCode(string $countryCode): array
    {
        $isDomestic = $countryCode === self::DOMESTIC_COUNTRY;
        $isInternational = !$isDomestic;
        $services = [];
        foreach ($this->getActiveServices() as $id => $service) {
            if ($isDomestic && $service->getDomestic()) {
                $services[$id] = $service;
                continue;
            }

            if ($isInternational && $service->getInternational()) {
                $services[$id] = $service;
            }
        }

        return $services;
    }

    /**
     * Get service model by code
     *
     * @param string $code
     * @param bool $isInternal
     * @return ServiceInterface|null
     * @throws LocalizedException|Throwable
     */
    public function getServiceByCode(string $code, bool $isInternal = false): ?ServiceInterface
    {
        foreach ($this->getAllServices() as $internalCode => $service) {
            $serviceCode = $isInternal ? $internalCode : $service->getCode();
            if ($serviceCode !== $code) {
                continue;
            }

            return $service;
        }

        return null;
    }
}
