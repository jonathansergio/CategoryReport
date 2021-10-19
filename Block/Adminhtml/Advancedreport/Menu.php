<?php

namespace Js\CategoryReport\Block\Adminhtml\Advancedreport;

/**
 * Class Menu
 *
 * @package Js\CategoryReport\Block\Adminhtml\Advancedreport
 */
class Menu extends \Magento\Backend\Block\Template
{
    /**
     * @var null|array
     */
    protected $items = null;

    /**
     * @var \Lof\CompanyProduct\Helper\Currency
     */
    protected $_currencyHelper;

    /**
     * @var \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory
     */
    protected $_symbolSystemFactory;

    /**
     * Custom currency symbol properties
     *
     * @var array
     */
    protected $_symbolsData = [];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var null
     */
    protected $_currentCurrencyCode = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Lof_All::menu.phtml';

    /**
     * Menu constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                    $context
     * @param \Js\CategoryReport\Helper\Currency                       $currencyHelper
     * @param \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory $symbolSystemFactory
     * @param \Magento\Framework\ObjectManagerInterface                  $objectManager
     * @param array                                                      $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context,
        \Js\CategoryReport\Helper\Currency $currencyHelper,
        \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory $symbolSystemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context);

        $this->_currencyHelper = $currencyHelper;
        $this->_symbolSystemFactory = $symbolSystemFactory;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
    }

    /**
     * @return array|array[]|null
     */
    public function getMenuItems()
    {
        if ($this->items === null) {
            $items = [
                'advancedreports_products' => [
                    'title' => __('Product Reports'),
                    'url' => '#',
                    'resource' => 'Js_CategoryReport::products',
                    'item' => [
                        'inventory' => [
                        'title' => __('Category Report'),
                        'url' => $this->getUrl('*/advancedreports_category/inventory'),
                        'resource' => 'Js_CategoryReport::inventory'
                        ]
                    ]

                ],
            ];

            foreach ($items as $index => $item) {
                if (array_key_exists('resource', $item)) {
                    if (!$this->_authorization->isAllowed($item['resource'])) {
                        unset($items[$index]);
                    }
                }
            }
            $this->items = $items;
        }

        return $this->items;
    }

    /**
     * @return mixed
     */
    public function getCurrentItem()
    {
        $items          = $this->getMenuItems();
        $controllerName = $this->getRequest()->getControllerName();
        if (array_key_exists($controllerName, $items)) {
            return $items[$controllerName];
        }

        return $items['page'];

    }

    /**
     * @param null $currency_code
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCurrencyCode( $currency_code = null )
    {
        if ( $currency_code ) {
            return $currency_code;
        }

        $requestData = $this->_objectManager->get(
            'Magento\Backend\Helper\Data'
        )->prepareFilterString(
            $this->getRequest()->getParam('filter')
        );

        $requestCurrency = isset($requestData['currency_code']) && $requestData['currency_code'] ? $requestData['currency_code'] : '';
        if ( $requestCurrency ) {
            return $requestCurrency;
        }

        if ( $this->_currentCurrencyCode === null ) {
            $storeIds =$this->getStoreIds();
            if ( ! $storeIds ) {
                $this->_currentCurrencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
            }

            if ( count($storeIds) == 1 ) {
                $this->_currentCurrencyCode = $this->_storeManager->getStore($storeIds[0])->getBaseCurrencyCode();;
            }

            if ( count($storeIds) > 1 ) {
                $this->_currentCurrencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
            }
        }

        return $this->_currentCurrencyCode;
    }

    /**
     * Get allowed store ids array intersected with selected scope in store switcher
     *
     * @return  array
     */
    protected function getStoreIds()
    {
        if ( $this->getRequest()->getParam( 'store_ids' ) ) {
            $storeIds = explode( ',', $this->getRequest()->getParam( 'store_ids' ) );
        } else {
            $storeIds = [];
        }
        // By default storeIds array contains only allowed stores
        $allowedStoreIds = array_keys( $this->_storeManager->getStores() );
        // And then array_intersect with post data for prevent unauthorized stores reports
        $storeIds = array_intersect( $allowedStoreIds, $storeIds );
        // If selected all websites or unauthorized stores use only allowed
        if ( empty( $storeIds ) ) {
            $storeIds = $allowedStoreIds;
        }
        // reset array keys
        $storeIds = array_values( $storeIds );

        return $storeIds;
    }

    /**
     * @param array $item
     * @return string
     */
    public function renderAttributes(array $item)
    {
        $result = '';
        if (isset($item['attr'])) {
            foreach ($item['attr'] as $attrName => $attrValue) {
                $result .= sprintf(' %s=\'%s\'', $attrName, $attrValue);
            }
        }

        return $result;

    }
    /**
     * @param $itemIndex
     * @return bool
     */
    public function isCurrent($itemIndex)
    {
        return $itemIndex == $this->getRequest()->getControllerName();

    }//end isCurrent()

    /**
     * @return \Magento\Directory\Model\Currency
     */
    public function getCurrency()
    {
        return $this->_currencyHelper->getCurrency();
    }

    /**
     * @return \Js\CategoryReport\Helper\Currency
     */
    public function getCurrencyHelper()
    {
        return $this->_currencyHelper;
    }

    /**
     * Returns Custom currency symbol properties
     *
     * @return array
     */
    public function getCurrencySymbolsData()
    {
        if (!$this->_symbolsData) {
            $this->_symbolsData = $this->_symbolSystemFactory->create()->getCurrencySymbolsData();
        }
        return $this->_symbolsData;
    }

}//end class
