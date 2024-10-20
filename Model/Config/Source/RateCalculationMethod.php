<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Config\Source;

use RadWorks\ShipStation\Model\Carrier\Rate\CalculationMethod\ItemPerPackage;
use RadWorks\ShipStation\Model\Carrier\Rate\CalculationMethod\ItemsPerPackage;
use Magento\Framework\Data\OptionSourceInterface;

class RateCalculationMethod implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ItemsPerPackage::METHOD_CODE => __('A: Combine Items Into Single Box If Possible'),
            ItemPerPackage::METHOD_CODE => __('B: Pull Rates individually for Each Item')
        ];
    }
}
