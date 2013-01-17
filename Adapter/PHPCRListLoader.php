<?php

namespace Symfony\Cmf\Bundle\BlockBundle\Adapter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Bundle\BlockBundle\Block\ListLoaderInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Query\QueryBuilder;
use PHPCR\Util\QOM\QueryBuilder as QOMQueryBuilder;

class PHPCRListLoader implements ListLoaderInterface
{
    private $dm;

    /**
     * @param ContainerInterface $container
     * @param $documentManagerName
     */
    public function __construct(ContainerInterface $container, $documentManagerName)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_phpcr')->getManager($documentManagerName);
    }

    /**
     * Get items that the list block template can render,
     * you use the settings from the block passed
     *
     * @param \Sonata\BlockBundle\Model\BlockInterface
     * @return array items that the block template can render
     */
    public function getItems(BlockInterface $block)
    {
        if (!is_null($block->getSetting('query_builder')) && $block->getSetting('maxItems', false)) {

            $queryBuilder = $block->getSetting('query_builder');

            // If a query builder was passed, it must be a closure, QueryBuilder or QOMQueryBuilder instance
            if (!($queryBuilder instanceof QueryBuilder || $queryBuilder instanceof QOMQueryBuilder || $queryBuilder instanceof \Closure)) {
                throw new \RuntimeException($queryBuilder, 'Doctrine\ODM\PHPCR\Query\QueryBuilder or PHPCR\Util\QOM\QueryBuilder or \Closure');
            }

            if ($queryBuilder instanceof \Closure) {
                $queryBuilder = $queryBuilder($this->dm);

                if (!($queryBuilder instanceof QueryBuilder or $queryBuilder instanceof QOMQueryBuilder)) {
                    throw new \RuntimeException($queryBuilder, 'Doctrine\ODM\PHPCR\Query\QueryBuilder or PHPCR\Util\QOM\QueryBuilder');
                }
            }

            // limit results
            $queryBuilder->setMaxItems($block->getSetting('maxItems'));

            // get items
            return $this->getDocuments($queryBuilder);

        } elseif ($block->getSetting('descendant', false) && $block->getSetting('maxItems', false)) {

            /** @var $qb \Doctrine\ODM\PHPCR\Query\QueryBuilder */
            $queryBuilder = $this->dm->createQueryBuilder();

            $queryBuilder
                ->andWhere($queryBuilder->expr()->descendant($block->getSetting('descendant')))
                ->setMaxResults($block->getSetting('maxItems'))
            ;

            if ($block->getSetting('orderByProperty', false)) {
                $queryBuilder->orderBy($block->getSetting('orderByProperty'), $block->getSetting('orderByDirection'));
            }

            // get items
            return $this->getDocuments($queryBuilder);

        } else {
            return array();
        }
    }

    /**
     * @param $queryBuilder \Doctrine\ODM\PHPCR\Query\QueryBuilder|\PHPCR\Util\QOM\QueryBuilder
     * @return array
     */
    private function getDocuments($queryBuilder)
    {
        if ($queryBuilder instanceof QOMQueryBuilder) {
            return $this->dm->getDocumentsByPhpcrQuery($queryBuilder->getQuery());
        } else {
            /** @var $queryBuilder QueryBuilder */
            return $queryBuilder->getQuery()->execute();
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
            'query_builder'    => null,
            'descendant'       => false,
            'orderByProperty'  => false,
            'orderByDirection' => 'ASC',
        );
    }
}