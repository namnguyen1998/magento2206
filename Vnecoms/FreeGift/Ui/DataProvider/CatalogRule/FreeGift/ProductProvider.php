<?php
namespace Vnecoms\FreeGift\Ui\DataProvider\CatalogRule\FreeGift;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Vnecoms\FreeGift\Model\CatalogRuleFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
/**
 * Class RelatedDataProvider
 */
class ProductProvider extends ProductDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;
       
    /**
     * @var \Vnecoms\FreeGift\Model\CatalogRule
     */
    private $rule;
    
    /**
     * @var CatalogRuleFactory
     */
    protected $catalogRuleFactory;
    
    /**
     * 
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param CatalogRuleFactory $catalogRuleFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        CatalogRuleFactory $catalogRuleFactory,
        StoreRepositoryInterface $storeRepository,
        ProductLinkRepositoryInterface $productLinkRepository,
        $addFieldStrategies,
        $addFilterStrategies,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
    
        $this->request = $request;
        $this->catalogRuleFactory = $catalogRuleFactory;
        $this->storeRepository = $storeRepository;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        /** @var Collection $collection */
        $collection = parent::getCollection();
        $collection->addAttributeToSelect('status');
        $collection->addAttributeToFilter(
            'type_id',
            ['in' => [
                ProductType::TYPE_SIMPLE,
                ProductType::TYPE_VIRTUAL
            ]]
        );

        return $collection;
    }
    
    /**
     * Retrieve product
     *
     * @return ProductInterface|null
     */
    protected function getRule()
    {
        if (null !== $this->rule) {
            return $this->rule;
        }
    
        if (!($id = $this->request->getParam('rule_id'))) {
            return null;
        }
    
        return $this->rule = $this->catalogRuleFactory->create()->load($id);
    }
}
