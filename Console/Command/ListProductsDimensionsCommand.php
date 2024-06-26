<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Console\Command;

use DmiRud\ShipStation\Model\Carrier\PackageBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Console\Cli;

class ListProductsDimensionsCommand extends Command
{
    public const NAME = 'shipstation:products-dimensions:list';
    private const OPTION_STORE_ID = 'store-id';
    private const OPTION_SHOW_MISSING_ONLY = 'show-missing-only';
    private ProductRepositoryInterface $productRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private FilterBuilder $filterBuilder;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        FilterBuilder              $filterBuilder,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder      $searchCriteriaBuilder,
        ScopeConfigInterface       $scopeConfig,
        string                     $name = self::NAME
    ) {
        parent::__construct($name);
        $this->filterBuilder = $filterBuilder;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription(
                'List products shipping dimensions/weight.'
            )->setDefinition([
                new InputOption(
                    self::OPTION_SHOW_MISSING_ONLY,
                    null,
                    InputOption::VALUE_NONE,
                    'Show only products missing shipping dimensions',
                ),
                new InputOption(
                    self::OPTION_STORE_ID,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Store id (1 by default)',
                    '1'
                )
            ]);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $rows = [];
            $filters = [$this->filterBuilder->setField('weight')->setConditionType('null')->create()];
            $attributes = array_map(fn($configPath) => $this->scopeConfig->getValue($configPath), PackageBuilder::XML_PATHS_DIMENSIONS);
            $missingOnly = $input->getOption(self::OPTION_SHOW_MISSING_ONLY);
            if ($missingOnly) {
                foreach ($attributes as $attrCode) {
                    $filters[] = $this->filterBuilder
                        ->setField($attrCode)
                        ->setConditionType('null')
                        ->create();
                }
                $this->searchCriteriaBuilder->addFilters($filters);
            }

            if ($input->getOption(self::OPTION_STORE_ID)) {
                $this->searchCriteriaBuilder
                    ->addFilter('store_id', $input->getOption(self::OPTION_STORE_ID));
            }
            $this->searchCriteriaBuilder
                ->addFilter('type_id', 'simple')
                ->addFilter('status', 1);
            $products = $this->productRepository
                ->getList($this->searchCriteriaBuilder->create())
                ->getItems();
            $headers = array_merge(['entity_id', 'sku', 'weight'], $attributes);
            foreach ($products as $product) {
                $rows[] = $product->toArray($headers);
            }
            $title = $missingOnly ? 'Products Missing Shipping Dimensions:' : 'Products Shipping Dimensions:';
            if ($rows) {
                $table = new Table($output);
                $table->setHeaders($headers);
                $table->addRows($rows);
                $output->writeln('<info>' . $title . '</info>');
                $table->render();
            } else {
                $output->writeln('<error>No Products Found.</error>');
            }

        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
