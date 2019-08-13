<?php
class Abcv_Dimensions_Block_Adminhtml_Dimensions_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
         
        // Set some defaults for our grid
        $this->setDefaultSort('ID');
        $this->setId('abcv_dimensions_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }
     
    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'abcv_dimensions/dimensions_collection';
    }
     
    protected function _prepareCollection()
    {
        // Get and set our collection for the grid
        $collection = Mage::getResourceModel($this->_getCollectionClass());
		//$collection->getSelect()->joinLeft(array('st' => 'core_store'),'main_table.SiteID = st.store_id',array('storename' => 'st.name'));
        $this->setCollection($collection);
         
        return parent::_prepareCollection();
    }
     
    protected function _prepareColumns()
    {
		//DDL search			
		$allStores = Mage::app()->getStores();//get all store public
		$ddl_store = array();
		foreach ($allStores as $_eachStoreId => $val){
			$_storeCode = Mage::app()->getStore($_eachStoreId)->getCode(); 
			$site_id = str_replace('sid','',$_storeCode);
			if ($site_id == 'default')
				$site_id = "6";
			$site_name = Mage::app()->getStore($_eachStoreId)->getName();			
			$ddl_store[$site_id] = $site_name;
		}       	
        // Add the columns that should appear in the grid
		$this->addColumn('ID', array(
		  'header'    => $this->__('ID'),
		  'align'     =>'left',
		  'width'     => '50px',
		  'index'     => 'ID',
		));

		$this->addColumn('siteid', array(
		  'header'    => $this->__('SiteID'),
		  'align'     =>'left',
		  'index'     => 'SiteID',
		  'width'     => '60px',
		));
		$this->addColumn('sitename', array(
		  'header'    => $this->__('Site Name'),
		  'align'     =>'left',
		  'index'     => 'SiteID',
		  'type'      => 'options',
		  'options'   => $ddl_store,
		));
		$this->addColumn('name', array(
		  'header'    => $this->__('Name'),
		  'align'     =>'left',
		  'index'     => 'Name',
		  'width'     => '35%',
		));
		$this->addColumn('w', array(
		  'header'    => $this->__('W'),
		  'align'     =>'left',
		  'index'     => 'W',
		  'width'     => '60px',
		));
		$this->addColumn('h', array(
		  'header'    => $this->__('H'),
		  'align'     =>'left',
		  'index'     => 'H',
		  'width'     => '60px',
		));
		$this->addColumn('radiow', array(
		  'header'    => $this->__('RatioW'),
		  'align'     =>'left',
		  'index'     => 'RatioW',
		  'width'     => '60px',
		));
		$this->addColumn('radioh', array(
		  'header'    => $this->__('RatioH'),
		  'align'     =>'left',
		  'index'     => 'RatioH',
		  'width'     => '60px',
		));
        $this->addColumn('action',
            array(
                'header'    =>  $this->__('Action'),
                'width'     => '60px',
                'type'      => 'action',
				'align'     =>'center',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => $this->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
        return parent::_prepareColumns();
    }
     
    public function getRowUrl($row)
    {
        // This is where our row data will link to
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}