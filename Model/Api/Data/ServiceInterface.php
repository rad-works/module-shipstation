<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Api\Data;

use DmiRud\ShipStation\Model\Carrier\ServiceRestrictionsInterface;

interface ServiceInterface extends EntityInterface
{
    public const FIELD_CARRIER_CODE = 'carrierCode';
    public const FIELD_CODE = 'code';
    public const FIELD_NAME = 'name';
    public const FIELD_DOMESTIC = 'domestic';
    public const FIELD_INTERNATIONAL = 'international';

    public function getInternalCode(): string;

    /**
     * Get carrier code
     *
     * @return string
     */
    public function getCarrierCode(): string;

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get is domestic
     *
     * @return bool
     */
    public function getDomestic(): bool;

    /**
     * Get is international
     *
     * @return bool
     */
    public function getInternational(): bool;

    /**
     * Get service restrictions
     *
     * @return ServiceRestrictionsInterface|null
     */
    public function getRestrictions(): ?ServiceRestrictionsInterface;

    /**
     * Set get carrier code
     *
     * @param string $carrierCode
     * @return ServiceInterface
     */
    public function setCarrierCode(string $carrierCode): ServiceInterface;

    /**
     * Set code
     *
     * @param string $code
     * @return ServiceInterface
     */
    public function setCode(string $code): ServiceInterface;

    /**
     * Set name
     *
     * @param string $name
     * @return ServiceInterface
     */
    public function setName(string $name): ServiceInterface;

    /**
     * Set is domestic
     *
     * @param bool $domestic
     * @return ServiceInterface
     */
    public function setDomestic(bool $domestic): ServiceInterface;

    /**
     * Set is international
     *
     * @param bool $international
     * @return ServiceInterface
     */
    public function setInternational(bool $international): ServiceInterface;

    /**
     * Set service restrictions
     *
     * @param ServiceRestrictionsInterface|null $restrictions
     * @return ServiceInterface
     */
    public function setRestrictions(ServiceRestrictionsInterface|null $restrictions): ServiceInterface;
}
