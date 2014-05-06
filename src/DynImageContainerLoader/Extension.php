<?php

namespace DynImageDIC;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;



/**
 * Description of DynImageExtension
 *
 * @author pascal.roux
 */
class Extension implements ExtensionInterface {

    public function load(array $configs, ContainerBuilder $container) {

        $loader = new XmlFileLoader(
                        $container,
                        new FileLocator(__DIR__)
        );
        $loader->load('dynimage.xml');
       
    }

    public function getAlias() {
        return '_ContainerLoader';
    }

    public function getXsdValidationBasePath() {
        return false;
    }

    public function getNamespace() {
        return false;
    }

}
