<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Api\Data;

interface CarrierInterface extends EntityInterface
{
    public const FIELD_NAME = 'name';
    public const FIELD_CODE = 'code';
    public const FIELD_ACCOUNT_NUMBER = 'accountNumber';
    public const FIELD_REQUIRES_FUNDED_ACCOUNT = 'requiresFundedAccount';
    public const FIELD_BALANCE = 'balance';
    public const FIELD_NICKNAME = 'nickname';
    public const FIELD_SHIPPING_PROVIDER_ID = 'shippingProviderId';
    public const FIELD_PRIMARY = 'primary';

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * Get account number
     *
     * @return string|null
     */
    public function getAccountNumber(): ?string;

    /**
     * Get is required funded account
     *
     * @return bool|null
     */
    public function getRequiresFundedAccount(): ?bool;

    /**
     * Get balance
     *
     * @return float
     */
    public function getBalance(): float;

    /**
     * Get nickname
     *
     * @return string|null
     */
    public function getNickname(): ?string;

    /**
     * Get shipping provider id
     *
     * @return int|null
     */
    public function getShippingProviderId(): ?int;

    /**
     * Get primary
     *
     * @return bool|null
     */
    public function getPrimary(): ?bool;

    /**
     * Set name
     *
     * @param string $name
     * @return CarrierInterface
     */
    public function setName(string $name): CarrierInterface;

    /**
     * Set code
     *
     * @param string $code
     * @return CarrierInterface
     */
    public function setCode(string $code): CarrierInterface;

    /**
     * Set account number
     *
     * @param string|null $accountNumber
     * @return CarrierInterface
     */
    public function setAccountNumber(?string $accountNumber): CarrierInterface;

    /**
     * Set account number
     *
     * @param bool $requiresFundedAccount
     * @return CarrierInterface
     */
    public function setRequiresFundedAccount(bool $requiresFundedAccount): CarrierInterface;

    /**
     * Set balance
     *
     * @param float $balance
     * @return CarrierInterface
     */
    public function setBalance(float $balance): CarrierInterface;

    /**
     * Set account number
     *
     * @param string|null $nickname
     * @return CarrierInterface
     */
    public function setNickname(string|null $nickname): CarrierInterface;

    /**
     * Set shipping provider id
     *
     * @param int|null $shippingProviderId
     * @return CarrierInterface
     */
    public function setShippingProviderId(int|null $shippingProviderId): CarrierInterface;

    /**
     * Set primary
     *
     * @param bool $primary
     * @return CarrierInterface
     */
    public function setPrimary(bool $primary): CarrierInterface;
}
