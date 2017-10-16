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

        $rootNode
            ->children()
                ->arrayNode('rules')
                    ->useAttributeAsKey('pattern')
                    ->prototype('array')
                    ->prototype('scalar')
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
