<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Exception;

use Magento\Framework\Exception\LocalizedException;
use DmiRud\ShipStation\Model\Carrier\PackageInterface;

class NoServiceFoundForProduct extends LocalizedException
{
    public const ERROR_CODE = '100';

    public function __construct(PackageInterface $package = null)
    {
        parent::__construct(__("No service found for the package"), null, self::ERROR_CODE);
    }
}
