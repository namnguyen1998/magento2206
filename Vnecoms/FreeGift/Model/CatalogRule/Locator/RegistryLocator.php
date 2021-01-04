<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Model\CatalogRule\Locator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;

/**
 * Class RegistryLocator
 */
class RegistryLocator implements LocatorInterface
{
    /**
     * @var Registry
     */
    private $_registry;

    /**
     * @var \Vnecoms\FreeGift\Model\CatalogRule
     */
    private $_rule;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->_registry = $registry;
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function getRule()
    {
        if (null !== $this->_rule) {
            return $this->_rule;
        }

        if ($rule = $this->_registry->registry('current_rule')) {
            return $this->_rule = $rule;
        }

        throw new NotFoundException(__('The rule was not registered'));
    }


    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        return $this->getRule()->getWebsiteIds();
    }
}
