<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier;

use DmiRud\ShipStation\Model\Carrier\Rate\CalculationMethod\ItemsPerPackage;
use DmiRud\ShipStation\Model\Carrier\Rate\CalculationMethod\ItemPerPackage;
use Exception;
use Magento\Framework\ObjectManagerInterface;

class RateCalculationMethodFactory
{
    private const MAP = [
        ItemPerPackage::METHOD_CODE => ItemPerPackage::class,
        ItemsPerPackage::METHOD_CODE => ItemsPerPackage::class
    ];

    public function __construct(private readonly ObjectManagerInterface $objectManager)
    {
    }


    /**
     * Get rate calculation method instance
     *
     * @param string $methodCode
     * @return RateCalculationMethodInterface
     * @throws Exception
     */
    public function create(string $methodCode): RateCalculationMethodInterface
    {
        /** @var RateCalculationMethodInterface $method */
        $method = $this->objectManager->create(self::MAP[$methodCode]);
        if (!$method instanceof RateCalculationMethodInterface) {
            throw new Exception('Object is not instance of RateCalculationMethodInterface');
        }

        return $method;
    }
}