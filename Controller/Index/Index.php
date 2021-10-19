<?php

namespace Js\CategoryReport\Controller\Index;

use Js\CategoryReport\Block\Example as BlockExample;
use Js\CategoryReport\Model\AllCategory as AllCategory;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	protected $blockExample;
	protected $allCategory;

    protected $_storeManager;
    protected $_categoryCollection;

    /**
     * @var Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     */
    protected $statusCollectionFactory;

    protected $_categoryHelper;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory,
        BlockExample $blockExample,
        AllCategory $allCategory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    )
	{
		$this->_pageFactory = $pageFactory;
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->blockExample = $blockExample;
        $this->allCategory = $allCategory;
        $this->_storeManager = $storeManager;
        $this->_categoryCollection = $categoryCollection;
		return parent::__construct($context);
	}

	public function execute()
	{
        var_dump($this->getCategories());
	}

    /**
     * Get status options
     *
     * @return array
     */
    public function getStatusOptions()
    {
        $options = $this->statusCollectionFactory->create()->toOptionArray();
        return $options;
    }

    public function getCategories(){
        $categories = $this->_categoryCollection->create()
            ->addAttributeToSelect('*')
            ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched

        foreach ($categories as $category){
            echo nl2br($category->getName()."\n");
        }
    }

}
