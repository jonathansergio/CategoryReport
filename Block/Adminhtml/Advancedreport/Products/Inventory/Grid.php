<?php

namespace Js\CategoryReport\Block\Adminhtml\Advancedreport\Products\Inventory;

use Magento\Store\Model\Store;

class Grid extends \Js\CategoryReport\Block\Adminhtml\Grid\AbstractGrid
{

    protected $_columnDate = 'main_table.created_at';
    protected $_columnGroupBy = '';
    protected $_defaultSort = 'period';
    protected $_defaultDir = 'ASC';
    protected $_resource_grid_collection = null;
    protected $_scopeconfig;

    public function _construct(
    )
    {
        parent::_construct();
        $this->setId('inventoryGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setFilterVisibility(true);
        $this->setUseAjax(false);
    }
 /**
     * {@inheritdoc}
     */
 public function getResourceCollectionName()
 {
    return 'Js\CategoryReport\Model\ResourceModel\Products\Collection';
}

protected function _getStore()
{
    $storeId = (int)$this->getRequest()->getParam('store', 0);
    return $this->_storeManager->getStore($storeId);
}

protected function _prepareColumns()
{
    $this->addColumn('entity_id',
        array(
            'header'=>  __('ID'),
            'width' => '50px',
            'type'  => 'number',
            'index' => 'entity_id',
        ));
    $this->addColumn('name',
        array(
            'header'=> __('Name'),
            'index' => 'name',
        ));

    $store = $this->_getStore();
    if ($store->getId()) {
        $this->addColumn('custom_name',
            array(
                'header'=> __('Name in %s', $store->getName()),
                'index' => 'custom_name',
            ));
    }

    $this->addColumn('sku',
        array(
            'header'=> __('SKU'),
            'width' => '80px',
            'index' => 'sku',
        ));

    $store = $this->_getStore();

    $filterData = $this->getFilterData();

    if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
        $this->addColumn('qty',
            array(
                'header'=> __('Qty'),
                'width' => '100px',
                'type'  => 'number',
                'index' => 'qty',
            ));
    }

    $this->addColumn('last_purchase',
        array(
            'header'=> __('Última Compra'),
            'width' => '100px',
            'type'  => 'date',
            'index' => 'last_purchase',
        )
    );

    $this->addColumn('status',
        array(
            'header'=> __('Status'),
            'width' => '70px',
            'index' => 'status',
            'type'  => 'options',
            'options' => $this->_objectManager->create('\Magento\Catalog\Model\Product\Attribute\Source\Status')->getOptionArray(),
        ));

    $this->addExportType('*/*/exportInventoryCsv', __('CSV'));
    $this->addExportType('*/*/exportInventoryExcel', __('Excel XML'));

    return parent::_prepareColumns();
}
protected function _prepareCollection()
{
    $storeIds  = $this->_getStoreIds();
    $filterData = $this->getFilterData();
    $report_type = $this->getReportType();
    $store = $this->_getStore();
    $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect(
        'sku'
    )->addAttributeToSelect(
        'name'
    )->addAttributeToSelect(
        'attribute_set_id'
    )->addAttributeToSelect(
        'type_id'
    )->setStore(
        $store
    );

    $category = $filterData->getData('category');

    if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
        $collection->joinField(
            'qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );
    }
    if ($category != null && $category != 0) {
        $collection->joinField(
            'ccp',
            'catalog_category_product',
            'category_id',
            'product_id=entity_id',
            '{{table}}.category_id='.$category,
            'inner'
        );
    }
    if ($store->getId()) {
        $collection->addStoreFilter($store);
        $collection->joinAttribute(
            'name',
            'catalog_product/name',
            'entity_id',
            null,
            'inner',
            Store::DEFAULT_STORE_ID
        );
        $collection->joinAttribute(
            'custom_name',
            'catalog_product/name',
            'entity_id',
            null,
            'inner',
            $store->getId()
        );
        $collection->joinAttribute(
            'status',
            'catalog_product/status',
            'entity_id',
            null,
            'inner',
            $store->getId()
        );
        $collection->joinAttribute(
            'visibility',
            'catalog_product/visibility',
            'entity_id',
            null,
            'inner',
            $store->getId()
        );
        $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
    } else {
        $collection->addAttributeToSelect('price');
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
    }

    $category_ids   = $filterData->getData('category_ids');

    if( $category_ids ){
        $category_id = explode(',', $category_ids);
        $collection->addCategoriesFilter(['in' => $category_id]);
    }

    $product_sku    = $filterData->getData('product_sku');

    if( $product_sku ){
        $collection->addAttributeToFilter('sku', array('eq' => $product_sku));
    }

    $qty_from       = $filterData->getData('qty_from');
    $qty_to         = $filterData->getData('qty_to');

    if( $qty_from && $qty_to ){
        $collection->getSelect()->where('at_qty.qty BETWEEN ' . $qty_from . ' AND ' . $qty_to. '');
    }
    $currencyCode       = $this->getCurrentCurrencyCode(null);
    $resourceProductCollection = $this->_objectManager->create($this->getResourceCollectionName())
    ->setOrderRate($currencyCode)
    ->prepareInventoryCollection()
    ->setMainTableId("product_id")
    ->setDateColumnFilter('created_at')
    ->addStoreFilter($storeIds);

    $order_statuses = $filterData->getData('order_statuses');
    $resourceProductCollection->addOrderStatusFilter($filterData->getData('order_statuses'));
    $resourceProductCollection->getSelect()
    ->group('product_id');
    $resourceProductCollection->applyCustomFilter();

    $purchased_product_select = $resourceProductCollection->getSelect();

    if( is_null($order_statuses) || !$order_statuses ) {
        $collection->getSelect()->joinLeft(array('payment'=>$purchased_product_select),'e.entity_id=payment.product_id');
    }else{
        $collection->getSelect()->join(array('payment'=>$purchased_product_select),'e.entity_id=payment.product_id');
    }

    $select =
        '
            SELECT
                product_id,
                MAX(created_at) AS `last_purchase`
            FROM `sales_order_item`
            GROUP BY product_id
        ';

    $collection->getSelect()
    ->joinLeft(
        array('soi' => new \Zend_Db_Expr(" ( $select ) ")),
        'soi.product_id = e.entity_id'
    );

    $this->setCollection($collection);
    $this->getCollection()->addWebsiteNamesToResult();
    parent::_prepareCollection();


    return $this;
}
        /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
        protected function _addColumnFilterToCollection($column)
        {
            if ($this->getCollection()) {
                if ($column->getId() == 'websites') {
                    $this->getCollection()->joinField(
                        'websites',
                        'catalog_product_website',
                        'website_id',
                        'product_id=entity_id',
                        null,
                        'left'
                    );
                }
            }
            return parent::_addColumnFilterToCollection($column);
        }

    }
