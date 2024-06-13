<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Carrier\Rate\CalculationMethod;

use DmiRud\ShipStation\Exception\NoPackageCreatedForService;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\Item;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\PackageResult;
use Magento\Shipping\Model\Rate\Result as RateResult;
use DmiRud\ShipStation\Exception\NoServiceFoundForProduct;
use DmiRud\ShipStation\Model\Api\RequestInterface;
use DmiRud\ShipStation\Model\Carrier;
use DmiRud\ShipStation\Model\Carrier\Rate\CalculationMethodAbstract;

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
     * @TODO add integrity validation logic for a case in which products have different carriers available
     */
    public function collectRequests(RateRequest $rateRequest, DataObject $rawRateRequest): array
    {
        $requests = [];
        $countryCode = $rawRateRequest->getDestCountry();
        $products = [];
        /** @var Item $item */
        foreach ($rateRequest->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            $qty = $item->getQty();
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

        //@TODO handle no requests or no service cases

        return $requests;
    }
}
