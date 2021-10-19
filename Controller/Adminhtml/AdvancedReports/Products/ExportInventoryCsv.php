<?php

namespace Js\CategoryReport\Controller\Adminhtml\AdvancedReports\Products;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportInventoryCsv extends \Js\CategoryReport\Controller\Adminhtml\AdvancedReports\Products\Inventory
{
    /**
     * Export bestsellers report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'Inventory.csv';
        $grid = $this->_view->getLayout()->createBlock('Js\CategoryReport\Block\Adminhtml\Advancedreport\Products\Inventory\Grid');
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getCsvFile(), DirectoryList::VAR_DIR);
    }

}
