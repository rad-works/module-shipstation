<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Api;

use DmiRud\ShipStation\Model\Api\Data\ServiceInterface;
use DmiRud\ShipStation\Model\Carrier\PackageInterface;

interface RequestInterface
{
    public const FIELD_ID = '_id';
    public const FIELD_PAYLOAD = 'payload';
    public const FIELD_PACKAGE = 'package';
    public const FIELD_SERVICE = 'service';

    public function getId(): string;

    public function getPayload(): array;

    public function getPayloadSerialized(): string;

    public function getPackage(): PackageInterface;

    public function getService(): ServiceInterface;

    public function setId(string $id): RequestInterface;

    public function setPayload(array $payload): RequestInterface;
    public function setPayloadSerialized(string $payloadSerialized): RequestInterface;

    public function setPackage(PackageInterface $package): RequestInterface;

    public function setService(ServiceInterface $service): RequestInterface;
}
