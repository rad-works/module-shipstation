<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Api\Data;

use Magento\Framework\DataObject;
use DmiRud\ShipStation\Model\Carrier\ServiceRestrictionsInterface;

class Service extends DataObject implements ServiceInterface
{
    public const FIELD_INTERNAL_CODE = 'internal_code';
    public const FIELD_CUSTOM_RESTRICTIONS = 'custom_restrictions';
    public const INTERNAL_CODE_DIVIDER = ':';

    public function getInternalCode(): string
    {
        if (!$this->hasData(self::FIELD_INTERNAL_CODE)) {
            $this->setData(self::FIELD_INTERNAL_CODE, sprintf(
                '%s' . self::INTERNAL_CODE_DIVIDER . '%s',
                $this->getCarrierCode(),
                $this->getCode()
            ));
        }

        return $this->getData(self::FIELD_INTERNAL_CODE);
    }

    public function getCode(): string
    {
        return $this->getData(self::FIELD_CODE);
    }

    public function getCarrierCode(): string
    {
        return $this->getData(self::FIELD_CARRIER_CODE);
    }

    public function getName(): string
    {
        return $this->getData(self::FIELD_NAME);
    }

    public function getDomestic(): bool
    {
        return $this->getData(self::FIELD_DOMESTIC);
    }

    public function getInternational(): bool
    {
        return $this->getData(self::FIELD_INTERNATIONAL);
    }

    public function getRestrictions(): ?ServiceRestrictionsInterface
    {
        return $this->getData(self::FIELD_CUSTOM_RESTRICTIONS);
    }

    public function setCarrierCode(string $carrierCode): ServiceInterface
    {
        return $this->setData(self::FIELD_CARRIER_CODE, $carrierCode);
    }

    public function setCode(string $code): ServiceInterface
    {
        return $this->setData(self::FIELD_CODE, $code);
    }

    public function setName(string $name): ServiceInterface
    {
        return $this->setData(self::FIELD_NAME, $name);
    }

    public function setDomestic(bool $domestic): ServiceInterface
    {
        return $this->setData(self::FIELD_DOMESTIC, $domestic);
    }

    public function setInternational(bool $international): ServiceInterface
    {
        return $this->setData(self::FIELD_INTERNATIONAL, $international);
    }

    public function setRestrictions(?ServiceRestrictionsInterface $restrictions): ServiceInterface
    {
        return $this->setData(self::FIELD_CUSTOM_RESTRICTIONS, $restrictions);
    }
}
