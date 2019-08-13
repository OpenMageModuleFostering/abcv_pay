<?php
class Abcv_Managecustom_Block_Adminhtml_Custom_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
         
        // Set some defaults for our grid
        $this->setDefaultSort('id');
        $this->setId('abcv_managecustom_custom_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }
     
    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'abcv_managecustom/custom_collection';
    }
     
    protected function _prepareCollection()
    {
        // Get and set our collection for the grid
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
         
        return parent::_prepareCollection();
    }
     
    protected function _prepareColumns()
    {
        // Add the columns that should appear in the grid
        $this->addColumn('id',
            array(
                'header'=> $this->__('ID'),
                'align' =>'center',
                'width' => '50px',
                'index' => 'id'
            )
        );
         
        $this->addColumn('order_id',
            array(
                'header'=> $this->__('Order Id'),
				'align' => 'right',
                'width' => '80px',
                'index' => 'order_id'
            )
        );
        
		$this->addColumn('employer',
            array(
                'header'=> $this->__('Employer Name'),
				'width' => '35%',
                'index' => 'employer'
            )
        );
         $this->addColumn('occupation',
            array(
                'header'=> $this->__('Occupation'),
                'index' => 'occupation'
            )
        );
         
        return parent::_prepareColumns();
    }
     
    public function getRowUrl($row)
    {
        return '';
    }
}