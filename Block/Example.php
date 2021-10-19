<?php

namespace Js\CategoryReport\Block;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\View\Element\Template;

class Example extends \Magento\Framework\View\Element\Template
{
    protected $_categoryHelper;
    protected $categoryFlatConfig;
    protected $topMenu;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Theme\Block\Html\Topmenu $topMenu
    ) {
        $this->_categoryHelper = $categoryHelper;
        $this->_coreRegistry = $registry;
        $this->categoryFlatConfig = $categoryFlatState;
        $this->topMenu = $topMenu;
        parent::__construct($context);
    }
    /**
     * Return categories helper
     */
    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }

    public function getCurrentCategory(){
        return $this->_coreRegistry->registry('current_category');
    }
public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
    }
    /**
     * Retrieve child store categories
     *
     */
    public function getChildCategories($category)
    {
        if ($this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }
        return $subcategories;
    }
}
