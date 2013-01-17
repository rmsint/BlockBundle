<?php

namespace Symfony\Cmf\Bundle\BlockBundle\Block;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Sonata\BlockBundle\Block\BlockServiceInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Block\BaseBlockService;

class ListBlockService extends BaseBlockService implements BlockServiceInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string default template
     */
    private $template;

    /**
     * @param $name
     * @param \Symfony\Component\Templating\EngineInterface $templating
     */
    public function __construct($name, EngineInterface $templating, ContainerInterface $container)
    {
        parent::__construct($name, $templating);
        $this->container = $container;
        $this->template = 'SymfonyCmfBlockBundle:Block:block_list.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
        // Not used at the moment, editing using a frontend or backend UI could be changed here
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // Not used at the moment, validation for editing using a frontend or backend UI could be changed here
    }

    /**
     * @param $id
     * @return ListInterface
     * @throws \RuntimeException
     */
    private function getService($id)
    {
        $service = $this->container->get($id);

        if (! $service instanceof ListLoaderInterface) {
            throw new \RuntimeException("Service '$id' is no Symfony\\Cmf\\Bundle\\BlockBundle\\Block\\ListLoaderInterface but " . get_class($service));
        }

        return $service;
    }

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function execute(BlockInterface $block, Response $response = null)
    {
        if (!$response) {
            $response = new Response();
        }

        if ($block->getEnabled()) {
            if (is_null($block->getSetting('serviceId'))) {
                throw new \RuntimeException('No serviceId defined.');
            }

            $listLoader = $this->getService($block->getSetting('serviceId'));

            $items = $listLoader->getItems($block);

            if (!is_array($items) && !$items instanceof \Traversable) {
                throw new \RuntimeException(
                    'The items should be an array or a \Traversable.'
                );
            }

            // merge settings
            $settings = array_merge(
                $this->getDefaultSettings(),
                $listLoader->getDefaultSettings(),
                $block->getSettings()
            );

            $template = isset($settings['template']) ? $settings['template'] : $this->template;

            $response = $this->renderResponse($template, array(
                'items'    => $items,
                'block'    => $block,
                'settings' => $settings,
            ), $response);
        }

        return $response;
    }

    /**
     * @param string $template default template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSettings()
    {
        return array(
            'serviceId' => null, // must implement ListLoaderInterface
            'template'  => $this->template,
            'title'     => 'Insert the list title',
            'maxItems'  => 10,
        );
    }
}
