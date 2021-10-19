<?php

namespace Js\CategoryReport\Controller\Adminhtml\AdvancedReports;

abstract class Order extends AbstractReport
{


    public function _initAction()
    {
        parent::_initAction();
        $this->_addBreadcrumb(__('Order'), __('Order'));
        return $this;
    }
}
