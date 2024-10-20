<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Api;

use Magento\Framework\DataObject;
use RadWorks\ShipStation\Model\Api\Data\ServiceInterface;
use RadWorks\ShipStation\Model\Carrier\PackageInterface;

interface RequestBuilderInterface
{
    public function build(PackageInterface $package, ServiceInterface $service, DataObject $rawRateRequest): RequestInterface;
}
