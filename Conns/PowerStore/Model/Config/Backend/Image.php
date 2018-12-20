<?php

namespace Conns\PowerStore\Model\Config\Backend;

class Image extends \Magento\Config\Model\Config\Backend\Image
{
    const UPLOAD_DIR = 'storelocator/gmap-marker';


    /**
     * @return string
     * @throw \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUploadDir()
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(self::UPLOAD_DIR));
    }

    /**
     * @return boolean
     */
    protected function _addWhetherScopeInfo()
    {
        return true;
    }

    /**
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png', 'svg'];
    }

}