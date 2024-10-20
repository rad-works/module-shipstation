<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Api;

use Magento\Framework\DataObject;
use RadWorks\ShipStation\Model\Api\Data\ServiceInterface;
use RadWorks\ShipStation\Model\Carrier\PackageInterface;

class Request extends DataObject implements RequestInterface
{
    public const FIELD_PAYLOAD_SERIALIZED = 'payload_serialized';

    public function getId(): string
    {
        return $this->getData(self::FIELD_ID);
    }

    public function getPayload(): array
    {
        return $this->getData(self::FIELD_PAYLOAD);
    }

    public function setId(string $id): RequestInterface
    {
        return $this->setData(self::FIELD_ID, $id);
    }

    public function getPackage(): PackageInterface
    {
        return $this->getData(self::FIELD_PACKAGE);
    }

    public function getPayloadSerialized(): string
    {
        return $this->getData(self::FIELD_PAYLOAD_SERIALIZED);
    }

    public function getService(): ServiceInterface
    {
        return $this->getData(self::FIELD_SERVICE);
    }

    public function setPayload(array $payload): RequestInterface
    {
        return $this->setData(self::FIELD_PAYLOAD, $payload);
    }

    public function setPayloadSerialized(string $payloadSerialized): RequestInterface
    {
        return $this->setData(self::FIELD_PAYLOAD_SERIALIZED, $payloadSerialized);
    }

    public function setPackage(PackageInterface $package): RequestInterface
    {
        return $this->setData(self::FIELD_PACKAGE, $package);
    }

    public function setService(ServiceInterface $service): RequestInterface
    {
        return $this->setData(self::FIELD_SERVICE, $service);
    }
}
