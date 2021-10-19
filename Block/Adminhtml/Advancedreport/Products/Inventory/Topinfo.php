<?php

namespace Js\CategoryReport\Block\Adminhtml\Advancedreport\Products\Inventory;

class Topinfo extends \Magento\Backend\Block\Template
{
    protected $_columnDate = 'main_table.created_at';
	protected $_limit = 10;
    protected $_storeManager;
    protected $_helperData;
    protected $_objectManager;
    protected $_registry;
    protected $_localeCurrency;

    /**
     * Format price by specified website
     *
     * @param float $price
     * @param null|int $websiteId
     * @return string
     */
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    public $localeLists;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Js\CategoryReport\Helper\Data $helperData,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        array $data = []
        )
    {
        parent::__construct($context, $data);
        $this->localeLists = $localeLists;
        $this->_helperData = $helperData;
        $this->_storeManager = $context->getStoreManager();
        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
        $this->_localeCurrency = $localeCurrency;
        $this->setTemplate("Js_CategoryReport::report/inventory_info.phtml");
    }
    public function formatCurrency($price, $websiteId = null)
    {
        return $this->_storeManager->getWebsite($websiteId)->getBaseCurrency()->format($price);
    }
    public function getSummary(){
        $summary = $this->_objectManager->create('Js\CategoryReport\Model\ResourceModel\Products\Collection')
            ->getSummary();
        return $summary;
    }
    public function getAvailableQty() {
        $qty = $this->_objectManager->create('Js\CategoryReport\Model\ResourceModel\Products\Collection')
            ->getAvailableQty();
        return number_format($qty['available_qty']);
    }
    public function getPurchasedQty() {
        $qty = $this->getSummary()['total_qty_ordered'];
        return number_format($qty);
    }

    public function getRevenue() {
        $total = $this->getSummary()['total_revenue_amount'];
        $websiteId = $this->getRequest()->getParam('website');
        return $this->formatCurrency($total, $websiteId);
    }
}
