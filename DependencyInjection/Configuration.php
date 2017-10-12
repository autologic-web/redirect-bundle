<?php

namespace Autologic\Bundle\RedirectBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('autologic_redirect');

        $rootNode->children()
            ->arrayNode('rules')
                ->defaultValue([])
            ->end();

        return $treeBuilder;
    }
}
