<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ApiType implements OptionSourceInterface
{
    public const API_CUSTOM_STORE = 'customer_store_pai';
    public const API_SHIP_STATION = 'shipstation_api';

    public function toOptionArray(): array
    {
        return [
            self::API_CUSTOM_STORE => __('Custom Store API (Default)'),
            self::API_SHIP_STATION => __('ShipStation API')
        ];
    }
}
