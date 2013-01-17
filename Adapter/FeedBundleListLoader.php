<?php

namespace Symfony\Cmf\Bundle\BlockBundle\Adapter;

use Symfony\Cmf\Bundle\BlockBundle\Block\ListLoaderInterface;
use Sonata\BlockBundle\Model\BlockInterface;

use Sandbox\MainBundle\Feed\Feed;
use Sandbox\MainBundle\Feed\Item;

class FeedBundleListLoader implements ListLoaderInterface
{
    /**
     * Get items that the list block template can render,
     * you use the settings from the block passed
     *
     * @param \Sonata\BlockBundle\Model\BlockInterface
     * @return array items that the block template can render
     */
    public function getItems(BlockInterface $block)
    {
        if ($block->getSetting('url', false) && $block->getSetting('maxItems', false)) {
            $channel = new Feed();

            $options = array(
                'http' => array(
                    'user_agent' => 'Symfony2 CMF/RSS Reader',
                    'timeout' => 2,
                )
            );

            // retrieve contents with a specific stream context to avoid php errors
            $content = @file_get_contents($block->getSetting('url'), false, stream_context_create($options));

            if ($content) {
                // generate a simple xml element
                try {
                    $xml = new \SimpleXMLElement($content);

                    // Initialize the channel/feed data array
                    $channel->setTitle($xml->channel->title);
                    $channel->setLink($xml->channel->link);
                    $channel->setDescription($xml->channel->description);

                    // Loop over each channel item/entry and store relevant data for each
                    foreach ($xml->channel->item as $item) {
                        $feedItem = new Item();
                        $feedItem->setTitle($item->title);
                        $feedItem->setLink($item->link);
                        $feedItem->setDescription($item->description);

                        $channel->addItem($feedItem);
                    }
                } catch(\Exception $e) {
                    // silently fail error
                    return array();
                }
            }

            return $channel->getItems()->slice(0, $block->getSetting('maxItems'));
        } else {
            return array();
        }
    }

    /**
     * Get a list of additional default settings for the list block
     *
     * @return array
     */
    public function getDefaultSettings()
    {
        return array(
            'url'      => false,
            'template' => 'SymfonyCmfBlockBundle:Block:block_rss.html.twig',
        );
    }
}