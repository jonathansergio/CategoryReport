<?php

namespace Js\CategoryReport\Block\Adminhtml\Advancedreport\Products;

class Inventory extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Template file
     *
     * @var string
     */
    protected $_template = 'report/grid/container.phtml';


    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Js_CategoryReport';
        $this->_controller = 'adminhtml_advancedreport_products_inventory';
        $this->_headerText = __('Report');
        parent::_construct();
        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            ['label' => __('Show Report'), 'onclick' => 'filterFormSubmit()', 'class' => 'primary']
        );
    }

    /**
     * Get filter URL
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/inventory', ['_current' => true]);
    }
    public function setReportType($type = "") {
        if($type) {
            $this->_report_type = $type;
        }
    }
}

