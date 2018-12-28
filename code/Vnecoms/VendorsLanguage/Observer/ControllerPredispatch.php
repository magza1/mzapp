<?php
namespace Vnecoms\VendorsLanguage\Observer;

use Magento\Framework\Event\ObserverInterface;

class ControllerPredispatch implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $translate;
    
    /**
     * @var \Vnecoms\VendorsConfig\Helper\Data
     */
    protected $vendorConfig;
    
    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $vendorSession;

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\TranslateInterface $translate
     * @param \Vnecoms\VendorsConfig\Helper\Data $vendorConfig
     * @param \Vnecoms\Vendors\Model\Session $vendorSession
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\TranslateInterface $translate,
        \Vnecoms\VendorsConfig\Helper\Data $vendorConfig,
        \Vnecoms\Vendors\Model\Session $vendorSession
    ) {
        $this->localeResolver = $localeResolver;
        $this->translate = $translate;
        $this->vendorConfig = $vendorConfig;
        $this->vendorSession = $vendorSession;
    }

    /**
     * Add the notification if there are any vendor awaiting for approval.
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vendorLocaleCode = $this->vendorConfig->getVendorConfig('general/locale/code', $this->vendorSession->getVendor()->getId());
        $this->localeResolver->setLocale($vendorLocaleCode);
        $this->translate->setLocale($vendorLocaleCode);
        $this->translate->loadData('vendors');
    }
}
