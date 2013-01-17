<?php

namespace Symfony\Cmf\Bundle\BlockBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;

/**
 * Block to display a list of items
 *
 * @PHPCRODM\Document(referenceable=true)
 */
class ListBlock extends BaseBlock
{
    public function getType()
    {
        return 'symfony_cmf.block.list';
    }
}
