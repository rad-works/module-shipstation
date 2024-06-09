<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Api;

use Magento\Framework\DataObject;
use DmiRud\ShipStation\Model\Api\Data\ServiceInterface;
use DmiRud\ShipStation\Model\Carrier\PackageInterface;

interface RequestBuilderInterface
{
    public function build(PackageInterface $package, ServiceInterface $service, DataObject $rawRateRequest): RequestInterface;
}
