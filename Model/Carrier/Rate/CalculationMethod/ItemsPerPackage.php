<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Model\Carrier\Rate\CalculationMethod;

use RadWorks\ShipStation\Exception\NoPackageCreatedForService;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\Item;
use Magento\Quote\Model\Quote\Address\RateRequest;
use RadWorks\ShipStation\Exception\NoServiceFoundForProduct;
use RadWorks\ShipStation\Model\Api\RequestInterface;

class ItemsPerPackage extends ItemPerPackage
{
    public const METHOD_CODE = 'items_per_package';

    /**
     * Prepares ShipStation API request models and its payload
     *
     * @param RateRequest $rateRequest
     * @param DataObject $rawRateRequest
     * @return RequestInterface[]
     * @throws NoServiceFoundForProduct|NoSuchEntityException
     */
    public function collectRequests(RateRequest $rateRequest, DataObject $rawRateRequest): array
    {
        $requests = [];
        $countryCode = $rawRateRequest->getDestCountry();
        $products = [];
        /** @var Item $item */
        foreach ($rateRequest->getAllItems() as $item) {
            if ($item->getParentItemId() || $item->getProduct()->isVirtual()) {
                continue;
            }

            $qty = $item->getQty() ?: $item->getQtyToAdd();
            $product = $this->productRepository->get($item->getSku());
            while ($qty--) {
                $products[] = $product;
            }
        }

        foreach ($this->dataProvider->getServicesByDestCountryCode($countryCode) as $service) {
            try {
                foreach ($this->packageBuilder->buildPacked($service, $products) as $package) {
                    $requests[] = $this->requestBuilder->build($package, $service, $rawRateRequest);
                }
            } catch (NoPackageCreatedForService) {
                continue;
            }
        }

        if ($products && !$requests) {
            throw new NoServiceFoundForProduct;
        }

        return $requests;
    }
}
