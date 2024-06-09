<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Config\Source;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class ProductAttribute implements OptionSourceInterface
{
    public function __construct(
        private readonly ProductAttributeRepositoryInterface $attributeRepository,
        private readonly SearchCriteriaBuilder               $searchCriteriaBuilder
    ) {
    }

    public function toOptionArray(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('frontend_input', 'text')->create();
        $options = [];
        foreach ($this->attributeRepository->getList($searchCriteria)->getItems() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $options[] = [
                'value' => $attributeCode,
                'label' => "[{$attributeCode}] " . $attribute->getStoreLabel()
            ];
        }

        return $options;
    }
}
