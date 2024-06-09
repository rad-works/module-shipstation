<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Api;

use DmiRud\ShipStation\Model\Api\Data\CarrierInterface;
use DmiRud\ShipStation\Model\Api\Data\ServiceInterface;

/**
 * Config for API related entities
 */
interface DataProviderInterface
{
    public const DOMESTIC_COUNTRY = 'US';

    /**
     * Get set of allowed carriers
     *
     * @return CarrierInterface[]
     */
    public function getActiveCarriers(): array;

    /**
     * Get set of allowed services
     *
     * @return ServiceInterface[]
     */
    public function getActiveServices(): array;

    /**
     * Get available carriers
     *
     * @return CarrierInterface[]
     * @throw LocalizedException
     */
    public function getAllCarriers(): array;

    /**
     * Get available services
     *
     * @return ServiceInterface[]
     * @throw LocalizedException
     */
    public function getAllServices(): array;

    /**
     * Get services by country code
     *
     * @param string $countryCode
     * @return ServiceInterface[]
     */
    public function getServicesByDestCountryCode(string $countryCode): array;

    /**
     * Get service model by code
     *
     * @param string $code
     * @param bool $isInternal
     * @return ServiceInterface|null
     */
    public function getServiceByCode(string $code, bool $isInternal = false): ?ServiceInterface;
}
