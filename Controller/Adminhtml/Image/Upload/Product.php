<?php

namespace Swissup\ProLabels\Controller\Adminhtml\Image\Upload;

class Product extends \Magento\Catalog\Controller\Adminhtml\Category\Image\Upload
{
    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Swissup_ProLabels::save');
    }
}
