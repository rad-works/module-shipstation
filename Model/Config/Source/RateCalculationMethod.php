<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RateCalculationMethod implements OptionSourceInterface
{
    public const OPTION_A = 'items_per_package';
    public const OPTION_B = 'item_per_package';

    public function toOptionArray(): array
    {
        return [
//            self::OPTION_A => __('Option A: Combine items into Single Box if possible'),
            self::OPTION_B => __('Option B: Pull Rates individually for Each Item')
        ];
    }
}
