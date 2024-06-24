<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Console\Command\CollectRates;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\App\Emulation as StoreEmulation;
use DmiRud\ShipStation\Model\Carrier;

class RatesProvider
{
    public function __construct(
        private readonly QuoteFactory               $quoteFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreEmulation             $emulation,
        private readonly State                      $state
    ) {
    }

    /**
     * Fetch shipping rates by emulating cart with product
     *
     * @param $postCode
     * @param $skus
     * @param $countryCode
     * @param $storeId
     * @param string $carrierCode
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function get($postCode, $skus, $countryCode, $storeId, string $carrierCode = Carrier::CODE): array
    {
        $this->state->setAreaCode(Area::AREA_FRONTEND);
        $this->emulation->startEnvironmentEmulation($storeId, force: true);
        $quote = $this->quoteFactory->create();
        $quantities = array_count_values($skus);
        foreach (array_map(fn($sku) => $this->productRepository->get($sku), array_unique($skus)) as $product) {
            $quote->addProduct($product, $quantities[$product->getSku()]);
        };

        $address = $quote->getShippingAddress();
        $address->setSameAsBilling(true);
        $address->setSaveInAddressBook(false);
        $address->setCollectShippingRates(true);
        $address->setLimitCarrier($carrierCode);
        $address->setPostcode($postCode);
        $address->setCountryId($countryCode);
        $address->setQuote($quote);
        $address->requestShippingRates();
        $rates = $address->getAllShippingRates();
        $this->emulation->stopEnvironmentEmulation();

        return $rates;
    }
}
