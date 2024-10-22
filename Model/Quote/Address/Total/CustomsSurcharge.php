<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Quote\Address\Total;

use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use RadWorks\ShipStation\Model\Api\DataProviderInterface;
use RadWorks\ShipStation\Model\Carrier;
use RadWorks\ShipStation\Model\Carrier\ServiceRestrictionsInterface;

class CustomsSurcharge extends AbstractTotal
{
    public const TOTAL_CODE = 'shipping_customs_surcharge';

    /**
     * @var DataProviderInterface $dataProvider
     */
    private readonly DataProviderInterface $dataProvider;

    /**
     * @var PriceCurrencyInterface $priceCurrency
     */
    private PriceCurrencyInterface $priceCurrency;

    /**
     * @param DataProviderInterface $dataProvider
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(DataProviderInterface $dataProvider, PriceCurrencyInterface $priceCurrency)
    {
        $this->dataProvider = $dataProvider;
        $this->priceCurrency = $priceCurrency;
        $this->setCode(self::TOTAL_CODE);
    }

    /**
     * Collect totals information about shipping
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(Quote $quote, ShippingAssignmentInterface $shippingAssignment, Total $total): static
    {
        parent::collect($quote, $shippingAssignment, $total);
        /** @var Address $address */
        $address = $shippingAssignment->getShipping()->getAddress();
        $baseSurchargeRate = $this->getBaseSurcharge($quote, $shippingAssignment) ?: 0;
        $baseSurchargeAmount = $baseSurchargeRate ? $total->getBaseTotalAmount('subtotal') * $baseSurchargeRate : 0;
        $surchargeAmount = $baseSurchargeAmount ? $this->priceCurrency->convert(
            $baseSurchargeAmount,
            $quote->getStore()
        ) : 0;
        $address->setShippingSurchargeAmount($surchargeAmount);
        $address->setBaseShippingSurchargeAmount($baseSurchargeAmount);
        $total->setTotalAmount($this->getCode(), $surchargeAmount);
        $total->setBaseTotalAmount($this->getCode(), $baseSurchargeAmount);

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
        if ($quote->isVirtual() || !$surcharge) {
            return [];
        }

        return [
            'code' => $this->getCode(),
            'title' => $this->getLabel(),
            'value' => (float)$surcharge,
        ];
    }

    /**
     * Get total label
     *
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __('Customs Surcharge');
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
