<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Config\Source;

use DmiRud\ShipStation\Model\Carrier\Rate\CalculationMethod\ItemPerPackage;
use DmiRud\ShipStation\Model\Carrier\Rate\CalculationMethod\ItemsPerPackage;
use Magento\Framework\Data\OptionSourceInterface;

class RateCalculationMethod implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ItemsPerPackage::METHOD_CODE => __('Option A: Combine items into Single Box if possible'),
            ItemPerPackage::METHOD_CODE => __('Option B: Pull Rates individually for Each Item')
        ];
    }
}
