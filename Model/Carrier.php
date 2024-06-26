<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model;

use Auctane\Api\Model\Carrier\Shipping as AuctaneCarrier;
use Laminas\Http\Client;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Async\CallbackDeferred;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\HttpException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateResultErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Sales\Model\Order\Shipment as OrderShipment;
use Magento\Shipping\Model\Rate\PackageResultFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\Result\ProxyDeferred;
use Magento\Shipping\Model\Rate\Result\ProxyDeferredFactory;
use Magento\Shipping\Model\Rate\ResultFactory as RateResultFactory;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use DmiRud\ShipStation\Exception\NoServiceFoundForProduct;
use DmiRud\ShipStation\Model\Api\AsyncClientInterface;
use DmiRud\ShipStation\Model\Api\Client as ApiClient;
use DmiRud\ShipStation\Model\Api\Data\RateInterface;
use DmiRud\ShipStation\Model\Api\RequestInterface;
use DmiRud\ShipStation\Model\Cache\Type\ApiResponse;
use DmiRud\ShipStation\Model\Carrier\RateCalculationMethodFactory;
use DmiRud\ShipStation\Model\Carrier\RateCalculationMethodInterface;
use DmiRud\ShipStation\Model\Config\Source\ApiType;

class Carrier extends AuctaneCarrier
{
    public const CODE = 'shipstation';
    public const RATE_REQUEST_STATUS_FAILED = 'rate_request_failed';
    public const RATE_REQUEST_STATUS_SUCCESS = 'rate_request_success';
    protected const RATES_CACHE_IDENTIFIER = self::CODE . '_rates';
    private static array $debug = [];

    protected static array $requests = [
        self::RATE_REQUEST_STATUS_SUCCESS => [],
        self::RATE_REQUEST_STATUS_FAILED => []
    ];
    private CacheInterface $cache;
    private ?RateRequest $request = null;
    private AsyncClientInterface $asyncClient;
    private PackageResultFactory $packageResultFactory;
    private ProxyDeferredFactory $proxyDeferredFactory;
    private RateCalculationMethodInterface $rateCalculationMethod;
    private RateCalculationMethodFactory $rateCalculationMethodFactory;
    private RateResultFactory $rateResultFactory;
    private MethodFactory $rateResultMethodFactory;
    private Json $serializer;

    public function __construct(
        ScopeConfigInterface         $scopeConfig,
        LoggerInterface              $logger,
        Security                     $xmlSecurity,
        ElementFactory               $xmlElFactory,
        RateResultFactory            $rateFactory,
        RateResultErrorFactory       $rateErrorFactory,
        MethodFactory                $rateMethodFactory,
        ResultFactory                $trackFactory,
        ErrorFactory                 $trackErrorFactory,
        StatusFactory                $trackStatusFactory,
        RegionFactory                $regionFactory,
        CountryFactory               $countryFactory,
        CurrencyFactory              $currencyFactory,
        Data                         $directoryData,
        StockRegistryInterface       $stockRegistry,
        Client                       $zendClient,
        StoreManagerInterface        $storeManager,
        ProductMetadataInterface     $productMetadata,
        WriterInterface              $configWriter,
        Cart                         $cart,
        AsyncClientInterface         $asyncClient,
        CacheInterface               $cache,
        RateCalculationMethodFactory $rateCalculationMethodFactory,
        RateResultFactory            $rateResultFactory,
        MethodFactory                $rateResultMethodFactory,
        PackageResultFactory         $packageResultFactory,
        ProxyDeferredFactory         $proxyDeferredFactory,
        Json                         $serializer,
        array                        $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateErrorFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $zendClient,
            $storeManager,
            $productMetadata,
            $configWriter,
            $cart,
            $data
        );
        $this->asyncClient = $asyncClient;
        $this->cache = $cache;
        $this->packageResultFactory = $packageResultFactory;
        $this->proxyDeferredFactory = $proxyDeferredFactory;
        $this->rateCalculationMethodFactory = $rateCalculationMethodFactory;
        $this->rateResultFactory = $rateResultFactory;
        $this->rateResultMethodFactory = $rateResultMethodFactory;
        $this->serializer = $serializer;
    }

    /**
     * @return bool
     */
    public function canCollectRates(): bool
    {
        return parent::canCollectRates() && $this->getConfigData('active_services');
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return bool|Error|Result|ProxyDeferred
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->canCollectRates()) {
            return $this->getErrorMessage();
        }

        if ($this->getConfigData('api_type') == ApiType::API_CUSTOM_STORE) {
            return parent::collectRates($request);
        }

        if ($this->getConfigData('api_type') == ApiType::API_SHIP_STATION) {
            //To use the correct result in the callback.
            $this->_result = $result = $this->setRequest($request)->getQuotes();
            return $this->proxyDeferredFactory->create(
                [
                    'deferred' => new CallbackDeferred(
                        function () use ($request, $result) {
                            $this->_result = $result;
                            $this->_updateFreeMethodQuote($request);
                            return $this->_result;
                        }
                    )
                ]
            );
        }

        return false;
    }

    /**
     * Add postcode validation
     *
     * @param DataObject $request
     * @return DataObject|bool
     */
    public function processAdditionalValidation(DataObject $request)
    {
        if (!$request->getDestPostcode() && $this->isZipCodeRequired($request->getDestCountryId())) {
            $error = $this->getErrorMessage();
            return $error
                ? $error->setErrorMessage(__('This shipping method is not available. Please specify the zip code.'))
                : $error;
        }

        return parent::processAdditionalValidation($request);
    }

    /**
     * Get aggregated debug information
     *
     * @return array
     */
    public static function getDebugInfo(): array
    {
        return self::$debug;
    }

    private function setRequest(RateRequest $request): self
    {
        $rowRequest = new DataObject();
        $origCountry = $request->getOrigCountry() ?: $this->_scopeConfig->getValue(
            OrderShipment::XML_PATH_STORE_COUNTRY_ID,
            ScopeInterface::SCOPE_STORE,
            $request->getStoreId()
        );
        $rowRequest->setOrigCountry($this->_countryFactory->create()->load($origCountry)->getData('iso2_code'));
        $origRegionCode = $request->getOrigRegionCode() ?: $this->_scopeConfig->getValue(
            OrderShipment::XML_PATH_STORE_REGION_ID,
            ScopeInterface::SCOPE_STORE,
            $request->getStoreId()
        );
        $rowRequest->setOrigRegionCode(
            is_numeric($origRegionCode) ? $this->_regionFactory->create()->load($origRegionCode)->getCode() : $origRegionCode
        );
        $rowRequest->setOrigCity($request->getOrigCity() ?: $this->_scopeConfig->getValue(
            OrderShipment::XML_PATH_STORE_CITY,
            ScopeInterface::SCOPE_STORE,
            $request->getStoreId()
        ));
        $rowRequest->setOrigPostal($request->getOrigPostcode() ?: $this->_scopeConfig->getValue(
            OrderShipment::XML_PATH_STORE_ZIP,
            ScopeInterface::SCOPE_STORE,
            $request->getStoreId()
        ));
        $destCountry = $request->getDestCountryId() ?: self::USA_COUNTRY_ID;
        $rowRequest->setDestCountry(
            $this->_countryFactory->create()->load($destCountry)->getData('iso2_code') ?: $destCountry
        );
        $rowRequest->setDestRegionCode($request->getDestRegionCode());
        $rowRequest->setDestPostal($request->getDestPostcode());
        $rowRequest->setWeight($request->getPackageWeight() ?: 1);
        $rowRequest->setValue($request->getPackageValue());
        $rowRequest->setValueWithDiscount($request->getPackageValueWithDiscount());
        $rowRequest->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());
        $this->request = $request;
        $this->_rawRequest = $rowRequest;
        $this->rateCalculationMethod = $this->rateCalculationMethodFactory->create(
            $this->getConfigData('rate_calculation_method')
        );

        return $this;
    }

    /**
     * @return ProxyDeferred|Result
     */

    private function getQuotes(): ProxyDeferred|Result
    {
        try {
            $rateResponses = $this->collectRateResponses();
        } catch (NoServiceFoundForProduct) {
            return $this->getNoServiceFoundErrorMessage();
        }

        return $this->proxyDeferredFactory->create(
            [
                'deferred' => new CallbackDeferred(
                    function () use ($rateResponses) {
                        $results = [];
                        foreach ($rateResponses as $item) {
                            try {
                                /** @var RequestInterface $request */
                                [$request, $quoteCacheKey, $response] = $item;
                                $statusCode = 200;
                                $responseBody = $response;
                                /** @var Response|string $response */
                                if ($response instanceof HttpResponseDeferredInterface) {
                                    $statusCode = $response->get()->getStatusCode();
                                    $responseBody = $response->get()->getBody();
                                }
                                self::$debug[$quoteCacheKey]['response'] = $responseBody;
                                if (!($statusCode == 200 && str_contains((string)$responseBody, $request->getService()->getCode()))) {
                                    self::$requests[self::RATE_REQUEST_STATUS_FAILED][] = $request;
                                    self::$debug[$quoteCacheKey]['response_error'] = $responseBody;
                                    $this->_logger->warning('ShipStation API error:' . $responseBody);
                                    continue;
                                }
                            } catch (HttpException|LocalizedException $e) {
                                self::$requests[self::RATE_REQUEST_STATUS_FAILED][] = $request;
                                self::$debug[$quoteCacheKey]['response_error'] = $e->getMessage();
                                $this->_logger->critical($e);
                                continue;
                            }

                            self::$requests[self::RATE_REQUEST_STATUS_SUCCESS][] = $request;
                            self::$_quotesCache[$quoteCacheKey] = $responseBody;
                            $results[] = [$request, $responseBody];
                        }

                        $rates = $this->getRateResult($this, $results);
                        $this->saveResponseCache();

                        return $rates;
                    }
                )
            ]
        );
    }

    /**
     * Get rate models from API response data
     *
     * @param Carrier $carrier
     * @param array $responses
     * @return Result
     */
    public function getRateResult(Carrier $carrier, array $responses): Result
    {
        $packageResult = $this->packageResultFactory->create();
        foreach ($responses as $result) {
            $rateResult = $this->rateResultFactory->create();
            [$request, $response] = $result;
            foreach ($this->rateCalculationMethod->createRatesFromResponse($response) as $rate) {
                if ($rate->getService()->getCode() !== $request->getService()->getCode()) {
                    continue;
                }

                $request->getPackage()->setRate($rate);
                $rateMethod = $this->rateResultMethodFactory->create();
                $rateMethod->setCarrier($carrier->getCarrierCode());
                $rateMethod->setCarrierTitle($carrier->getConfigData('title'));
                $rateMethod->setMethod($rate->getServiceCode());
                $rateMethod->setMethodTitle($rate->getServiceName());
                $rateMethod->setCost($rate->getTotalCost());
                $rateMethod->setPrice($rateMethod->getCost());
                $rateResult->append($rateMethod);
            }

            $packageResult->appendPackageResult($rateResult, 1);
        }

        return $packageResult;
    }

    /**
     * Get ShipStation API rate responses
     *
     * @return array
     * @throws NoServiceFoundForProduct
     */
    private function collectRateResponses(): array
    {
        $requests = $this->rateCalculationMethod->collectRequests($this->request, $this->_rawRequest);
        $responses = [];
        $this->loadResponseCache();
        foreach ($requests as $request) {
            $quoteCacheKey = $this->_getQuotesCacheKey($request->getPayloadSerialized());
            self::$debug[$quoteCacheKey]['requests'][] = $request;
            if (array_key_exists($quoteCacheKey, self::$_quotesCache)) {
                $responses[] = [$request, $quoteCacheKey, self::$_quotesCache[$quoteCacheKey]];
                continue;
            }

            $responses[] = [$request, $quoteCacheKey, $this->asyncClient
                ->sendRequest(ApiClient::API_RATES_URL, body: $request->getPayloadSerialized())];
        }

        return $responses;
    }

    /**
     * Load ShipStation API responses from cache
     *
     * @return void
     */
    private function loadResponseCache(): void
    {
        try {
            if (!$cache = $this->cache->load(self::RATES_CACHE_IDENTIFIER)) {
                return;
            }

            $responses = $this->serializer->unserialize($cache)[self::RATES_CACHE_IDENTIFIER] ?? [];
            foreach ($responses as $quoteCacheKey => $quoteCache) {
                self::$_quotesCache[$quoteCacheKey] = $quoteCache;
            }
        } catch (\InvalidArgumentException) {
            return;
        }
    }

    /**
     * Save ShipStation API responses to cache
     *
     * @return void
     */
    private function saveResponseCache(): void
    {
        $responses = [];
        foreach (self::$_quotesCache as $quoteCacheKey => $quoteCache) {
            if ($quoteCache && str_contains($quoteCache, RateInterface::FIELD_SHIPMENT_COST)) {
                $responses[$quoteCacheKey] = $quoteCache;
            }
        }

        if ($responses) {
            $this->cache->save(
                $this->serializer->serialize([self::RATES_CACHE_IDENTIFIER => $responses]),
                self::RATES_CACHE_IDENTIFIER,
                [ApiResponse::CACHE_TAG],
                $this->getConfigData('rates_response_cache_lifetime') ?: null
            );
            $this->loadResponseCache();
        }
    }

    /**
     * @return Result
     */
    public function getNoServiceFoundErrorMessage(): Result
    {
        $result = $this->_rateFactory->create();
        $error = $this->_rateErrorFactory->create();
        $error->setCarrier($this->getCarrierCode())
            ->setCarrierTitle($this->getConfigData('title'))
            ->setErrorMessage('Shipment weight/size exceeds all allowed Shipping Methods, please contact us to resolve');
        $result->append($error);

        return $result;
    }

    /**
     * Get requests executed during carrier lifecycle
     *
     * @param string $status
     * @param bool $codesOnly
     * @return array
     */
    public static function getRequests(string $status, bool $codesOnly = false): array
    {
        $result = [];
        /** @var RequestInterface $request */
        foreach (static::$requests[$status] as $request) {
            $result[] = $codesOnly ? $request->getService()->getCode() : $request;
        }

        return $result;
    }
}
