<?php

namespace Symfony\Cmf\Bundle\BlockBundle\Block;

use Sonata\BlockBundle\Model\BlockInterface;

interface ListLoaderInterface
{
    /**
     * Get items that the list block template can render,
     * you use the settings from the block passed
     *
     * @param \Sonata\BlockBundle\Model\BlockInterface
     * @return array items that the block template can render
     */
    function getItems(BlockInterface $block);

    /**
     * Get a list of additional default settings for the list block
     *
     * @return array
     */
    function getDefaultSettings();
}