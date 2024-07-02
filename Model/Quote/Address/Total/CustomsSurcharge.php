<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Quote\Address\Total;

use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use DmiRud\ShipStation\Model\Api\DataProviderInterface;
use DmiRud\ShipStation\Model\Carrier;
use DmiRud\ShipStation\Model\Carrier\ServiceRestrictionsInterface;

class CustomsSurcharge extends AbstractTotal
{
    public const TOTAL_CODE = 'shipping_customs_surcharge';

    private readonly DataProviderInterface $dataProvider;

    /**
     * @param DataProviderInterface $dataProvider
     */
    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->setCode(self::TOTAL_CODE);
    }

    /**
     * Collect totals information about shipping
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function collect(Quote $quote, ShippingAssignmentInterface $shippingAssignment, Total $total): static
    {
        parent::collect($quote, $shippingAssignment, $total);
        $surcharge = 0;
        /** @var Address $address */
        $address = $shippingAssignment->getShipping()->getAddress();
        $total->setTotalAmount($this->getCode(), $surcharge);
        $total->setBaseTotalAmount($this->getCode(), $surcharge);
        $address->setShippingSurchargeAmount($surcharge);
        $address->setBaseShippingSurchargeAmount($surcharge);
        if (!$baseSurcharge = $this->getBaseSurcharge($quote, $shippingAssignment)) {
            return $this;
        }

        $surcharge = $total->getTotalAmount('subtotal') * $baseSurcharge;
        $total->addTotalAmount($this->getCode(), $surcharge);
        $total->addBaseTotalAmount($this->getCode(), $surcharge);
        $total->setTotalAmount($this->getCode(), $surcharge);
        $total->setBaseTotalAmount($this->getCode(), $surcharge);
        $address->setShippingSurchargeAmount($total->getTotalAmount($this->getCode()));
        $address->setBaseShippingSurchargeAmount($total->getBaseTotalAmount($this->getCode()));

        return $this;
    }

    /**
     * Add totals information to address object
     *
     * @param Quote $quote
     * @param Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, Total $total): array
    {
        $surcharge = $quote->getShippingAddress()->getShippingSurchargeAmount();
        if ($quote->getIsVirtual() && $surcharge) {
            return [];
        }

        return [
            'code' => $this->getCode(),
            'title' => $this->getLabel(),
            'value' => (float) $surcharge,
        ];
    }

    /**
     * Get total label
     *
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __('Custom\'s Surcharge');
    }

    /**
     * Get customs' surcharge rate
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return ServiceRestrictionsInterface|null
     */
    private function getBaseSurcharge(Quote $quote, ShippingAssignmentInterface $shippingAssignment): ?float
    {
        $shippingMethod = $shippingAssignment->getShipping()->getMethod() ?: $quote->getShippingAddress()->getShippingMethod();
        if (!($shippingMethod && $shippingAssignment->getItems())) {
            return null;
        }

        $address = $shippingAssignment->getShipping()->getAddress();
        foreach ($this->dataProvider->getServicesByDestCountryCode($address->getCountryId()) as $service) {
            if (Carrier::CODE . '_' . $service->getCode() !== $shippingMethod) {
                continue;
            }

            return $service->getRestrictions()?->getSubtotalAdjustment();
        }

        return null;
    }
}
