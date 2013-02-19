<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\Debug\DebugManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\Log\LogManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\Geocode\GeocoderManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\Mobiledetect\MobiledetectManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\GuessLocale\GuessLocaleManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\BlackWhiteList\BlackListManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\BlackWhiteList\WhiteListManager;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tech_g_sf_baseprj');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.       
        
        $this->addDebugSection($rootNode);
        $this->addLogSection($rootNode);
        $this->addGeoCodingSection($rootNode);
        $this->addGuessLocaleSection($rootNode);
        $this->addMobilDetectSection($rootNode);
        $this->addBlackListSection($rootNode);
        $this->addWhiteListSection($rootNode);
        
        return $treeBuilder;
    }
    
    
    private function addDebugSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode(DebugManager::MODULE_NAME)
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode(SettingManager::SUFFIX_ENABLE)
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }    
    
    private function addLogSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode(LogManager::MODULE_NAME)
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode(SettingManager::SUFFIX_ENABLE)
                            ->defaultFalse()
                        ->end()
                        ->scalarNode(LogManager::CONF_LOG_LEVEL)
                            ->defaultValue(1000)
                        ->end()
                        ->booleanNode(LogManager::CONF_ENABLE_QUEUE)
                            ->defaultTrue()
                        ->end()
                        ->booleanNode(LogManager::CONF_SAVE_SESSION)
                            ->defaultTrue()
                        ->end()
                        ->booleanNode(LogManager::CONF_SAVE_REQUEST)
                            ->defaultTrue()
                        ->end()
                        ->booleanNode(LogManager::CONF_SAVE_LAST_ACTIVITY)
                            ->defaultTrue()
                        ->end()
                        ->scalarNode(LogManager::CONF_SKIP_PATTERN)
                            ->defaultValue('')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }    
    
    private function addGeoCodingSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode(GeocoderManager::MODULE_NAME)
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode(SettingManager::SUFFIX_ENABLE)
                            ->defaultFalse()
                        ->end()
                        ->booleanNode(GeocoderManager::CONF_SAVE_SESSION)
                            ->defaultTrue()
                        ->end()                        
                    ->end()
                ->end()
            ->end();
    }    
    
    private function addGuessLocaleSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode(GuessLocaleManager::MODULE_NAME)
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode(SettingManager::SUFFIX_ENABLE)
                            ->defaultFalse()
                        ->end()
                        ->booleanNode(GuessLocaleManager::CONF_SAVE_SESSION)
                            ->defaultTrue()
                        ->end()                        
                        ->booleanNode(GuessLocaleManager::CONF_ONLY_FIRST_REQUEST)
                            ->defaultTrue()
                        ->end()                        
                    ->end()
                ->end()
            ->end();
    }    
    
    private function addMobilDetectSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode(MobiledetectManager::MODULE_NAME)
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode(SettingManager::SUFFIX_ENABLE)
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }    

    private function addBlackListSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode(BlackListManager::MODULE_NAME)
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode(SettingManager::SUFFIX_ENABLE)
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }    
    
    private function addWhiteListSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode(WhiteListManager::MODULE_NAME)
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode(SettingManager::SUFFIX_ENABLE)
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }        
}
