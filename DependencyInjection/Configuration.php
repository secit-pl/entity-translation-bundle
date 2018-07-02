<?php

declare(strict_types=1);

namespace SecIT\EntityTranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 *
 * @author Tomasz Gemza
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('entity_translation');
        $rootNode
            ->children()
                ->arrayNode('locales')
                    ->children()
                        ->arrayNode('defined')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->scalarPrototype()->end()
                        ->end()
                        ->scalarNode('default')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
