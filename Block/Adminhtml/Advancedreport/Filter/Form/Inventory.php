<?php

namespace Js\CategoryReport\Block\Adminhtml\Advancedreport\Filter\Form;

use Psr\Log\LoggerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Inventory extends \Js\CategoryReport\Block\Adminhtml\Advancedreport\Filter\Form
{

    protected $_storeManager;
    protected $_categoryCollection;
    protected $logger;

	public function __construct(
        StoreManagerInterface $storeManager,
        CollectionFactory $categoryCollection,
        LoggerInterface $logger,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\SalesRule\Model\ResourceModel\Report\RuleFactory $reportRule,
        array $data = []
    )
	{
        $this->_storeManager = $storeManager;
        $this->_categoryCollection = $categoryCollection;
        $this->logger = $logger;
        parent::__construct($context, $registry, $formFactory, $objectManager, $reportRule, $data);
	}

    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl($this->getFormActionUrl());
        $report_type = $this->getReportType();
        $notshow_actual = array("productsnotsold");

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'filter_form',
                    'action' => $actionUrl,
                    'method' => 'get'
                ]
            ]
        );
        $htmlIdPrefix = 'inventory_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Filter')]);

        $statuses = $this->_objectManager->create('Magento\Sales\Model\Order\Config')->getStatuses();

        $values = array();
        foreach ($statuses as $code => $label) {
            if (false === strpos($code, 'pending')) {
                $values[] = array(
                    'label' => __($label),
                    'value' => $code
                );
            }
        }

        $fieldset->addField('store_ids', 'hidden', array(
            'name'  => 'store_ids'
        ));

        $fieldset->addField('currency_code', 'hidden', array(
            'name'  => 'currency_code'
        ));

        $fieldset->addField('category', 'select', [
            'name'      => 'category',
            'label'     => __('Category'),
            'options'   => $this->getCategories(),
        ], 'to');

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }


    public function getCategories(){
        $categories = $this->_categoryCollection->create()
            ->addAttributeToSelect('*')
            ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched

        $categoriesArray = array();
        $categoriesArray[0] = ' ';
        foreach ($categories as $category){
            $categoriesArray[$category->getId()] = $category->getName();
        }

        return $categoriesArray;
    }
}
