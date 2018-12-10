<?php

namespace Conns\PowerStore\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Image extends \BrainActs\StoreLocator\Helper\Image
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    private $locatorDirectory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->locatorDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->storeManager = $storeManager;
        parent::__construct($context,$filesystem,$imageFactory,$storeManager);
    }

    public function resize($source,$width = 0,$height = 0)
    {
        $realPath = $this->locatorDirectory->getRelativePath($source);

        if (!$this->locatorDirectory->isFile($realPath) || !$this->locatorDirectory->isExist($realPath)) {
            if (!$this->scopeConfig->getValue(
                'brainacts_storelocator/item/use_placeholder',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )) {
                return false;
            }

            $placeholder = 'storelocator/placeholder/' .
                $this->scopeConfig->getValue(
                    'brainacts_storelocator/item/placeholder',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );

            if (!$this->locatorDirectory->isFile($placeholder) || !$this->locatorDirectory->isExist($placeholder)) {
                return false;
            }
            $source = $placeholder;
        }

        $targetDir = $this->getCachePath($source);
        $pathTargetDir = $this->locatorDirectory->getRelativePath($targetDir);
        if (!$this->locatorDirectory->isExist($pathTargetDir)) {
            $this->locatorDirectory->create($pathTargetDir);
        }

        if (!$this->locatorDirectory->isExist($pathTargetDir)) {
            return false;
        }

        $dest = $targetDir . '/' . pathinfo($source, PATHINFO_BASENAME);//@codingStandardsIgnoreLine

        $destRelativePath = $this->locatorDirectory->getRelativePath($dest);
        if (!$this->locatorDirectory->isExist($destRelativePath)) {
            $image = $this->imageFactory->create();
            $image->open($this->locatorDirectory->getAbsolutePath($source));
            $image->keepAspectRatio(true);
            if($width !== 0 && $height !== 0){
                $image->resize($width, $height);
            }
            $image->save($dest);
        }

        if ($this->locatorDirectory->isFile($this->locatorDirectory->getRelativePath($dest))) {
            $cachedUrl = $this->storeManager
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            $cachedUrl .= $this->locatorDirectory->getRelativePath($dest);

            return $cachedUrl;
        }
        return false;
    }

}