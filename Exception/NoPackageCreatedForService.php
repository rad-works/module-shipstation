<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Exception;

use DmiRud\ShipStation\Model\Api\Data\ServiceInterface;
use Magento\Framework\Exception\LocalizedException;

class NoPackageCreatedForService extends LocalizedException
{
    public const ERROR_CODE = '101';

    public function __construct(ServiceInterface $service = null)
    {
        $message = __("No package created according to service restrictions");
        if ($service) {
            $message = __('No package created according to "%s" restrictions', $service->getName());
        }

        parent::__construct($message, null, self::ERROR_CODE);
    }
}