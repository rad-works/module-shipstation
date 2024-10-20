<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Plugin\Model\Api\DataProviderInterface;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use RadWorks\ShipStation\Model\Api\Data\ServiceInterface;
use RadWorks\ShipStation\Model\Api\DataProviderInterface as ApiDataProviderInterface;
use RadWorks\ShipStation\Model\Carrier\ServiceRestrictionsInterface;
use RadWorks\ShipStation\Model\Carrier\ServiceRestrictionsInterfaceFactory;

/**
 * Add service restrictions data object to the service data object
 */
class AddServiceRestrictionsToService
{
    private const XML_PATH_ACTIVE_SERVICES = 'carriers/shipstation/service_restrictions';

    public function __construct(
        private readonly DataObjectHelper                    $dataObjectHelper,
        private readonly ScopeConfigInterface                $scopeConfig,
        private readonly SerializerInterface                 $serializer,
        private readonly ServiceRestrictionsInterfaceFactory $serviceRestrictionsFactory
    ) {
    }

    /**
     * Add ServiceRestrictions to Service model
     *
     * @param ApiDataProviderInterface $dataProvider
     * @param array $services
     * @return array
     */
    public function afterGetActiveServices(ApiDataProviderInterface $dataProvider, array $services): array
    {
        if (!($values = $this->getServiceRestrictionsConfigValues())) {
            return $services;
        };

        /**  @var ServiceInterface $service */
        foreach ($services as $fullCode => $service) {
            if (!array_key_exists($fullCode, $values) || $service->getRestrictions()) {
                continue;
            }

            $values[$fullCode][ServiceRestrictionsInterface::FIELD_SERVICE] = $service;
            $subTotalAdjustment = $values[$fullCode][ServiceRestrictionsInterface::FIELD_SUBTOTAL_ADJUSTMENT];
            $values[$fullCode][ServiceRestrictionsInterface::FIELD_SUBTOTAL_ADJUSTMENT] = $subTotalAdjustment ?: 0.0;
            $serviceRestrictions = $this->serviceRestrictionsFactory->create();
            $this->dataObjectHelper->populateWithArray($serviceRestrictions, $values[$fullCode], ServiceRestrictionsInterface::class);
            $service->setRestrictions($serviceRestrictions);
        }

        return $services;
    }

    private function getServiceRestrictionsConfigValues(): array
    {
        if (!($values = $this->scopeConfig->getValue(self::XML_PATH_ACTIVE_SERVICES) ?: '')) {
            return [];
        }

        return array_column(
            $this->serializer->unserialize($values),
            null,
            ServiceRestrictionsInterface::FIELD_SERVICE
        );
    }
}
