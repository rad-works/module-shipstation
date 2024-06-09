<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Plugin\Model\Carrier;

use Magento\Framework\DataObject;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use DmiRud\ShipStation\Model\Carrier;

/**
 * Allows the restriction of shipping methods based on the requester's IP address.
 */
class ValidateCustomerIp
{
    public function __construct(private readonly RemoteAddress $remoteAddress)
    {
    }

    /**
     * Check if requester IP is in the whitelist
     *
     * @param Carrier $carrier
     * @param DataObject|bool $result
     * @param DataObject $request
     * @return DataObject|bool
     */
    public function afterProcessAdditionalValidation(Carrier $carrier, DataObject|bool $result, DataObject $request)
    {
        $remoteAddr = $this->remoteAddress->getRemoteAddress();
        $allowedIps = $carrier->getConfigData('allowed_ips');
        if ($allowedIps
            &&
            $remoteAddr
            &&
            !in_array($remoteAddr, preg_split('#\s*,\s*#', $allowedIps, -1, PREG_SPLIT_NO_EMPTY))
        ) {
            return false;
        }

        return $result;
    }
}
