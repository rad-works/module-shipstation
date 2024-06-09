<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Api\Data;

use Magento\Framework\DataObject;

class Carrier extends DataObject implements CarrierInterface
{
    public function getName(): ?string
    {
        return $this->getData(self::FIELD_NAME);
    }

    public function getCode(): ?string
    {
        return $this->getData(self::FIELD_CODE);
    }

    public function getAccountNumber(): ?string
    {
        return $this->getData(self::FIELD_ACCOUNT_NUMBER);
    }

    public function getRequiresFundedAccount(): ?bool
    {
        return $this->getData(self::FIELD_REQUIRES_FUNDED_ACCOUNT);
    }

    public function getBalance(): float
    {
        return (float)$this->getData(self::FIELD_BALANCE);
    }

    public function getNickname(): ?string
    {
        return $this->getData(self::FIELD_NICKNAME);
    }

    public function getShippingProviderId(): ?int
    {
        return $this->getData(self::FIELD_SHIPPING_PROVIDER_ID);
    }

    public function getPrimary(): ?bool
    {
        return $this->getData(self::FIELD_PRIMARY);
    }

    public function setName(string $name): CarrierInterface
    {
        return $this->setData(self::FIELD_NAME, $name);
    }

    public function setCode(string $code): CarrierInterface
    {
        return $this->setData(self::FIELD_CODE, $code);
    }

    public function setAccountNumber(string|null $accountNumber): CarrierInterface
    {
        return $this->setData(self::FIELD_ACCOUNT_NUMBER, $accountNumber);
    }

    public function setRequiresFundedAccount(bool $requiresFundedAccount): CarrierInterface
    {
        return $this->setData(self::FIELD_REQUIRES_FUNDED_ACCOUNT, $requiresFundedAccount);
    }

    public function setBalance(float $balance): CarrierInterface
    {
        return $this->setData(self::FIELD_BALANCE, $balance);
    }

    public function setNickname(?string $nickname): CarrierInterface
    {
        return $this->setData(self::FIELD_NICKNAME, $nickname);
    }

    public function setShippingProviderId(?int $shippingProviderId): CarrierInterface
    {
        return $this->setData(self::FIELD_SHIPPING_PROVIDER_ID, $shippingProviderId);
    }

    public function setPrimary(bool $primary): CarrierInterface
    {
        return $this->setData(self::FIELD_PRIMARY, $primary);
    }
}
