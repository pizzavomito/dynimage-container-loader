<?php

namespace DynImageContainerLoader;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\FileLocator;

class ContainerLoader {

    static public function load($filename, $cache_dir, $debug = false, $extensions = array(), $reload=false, $className=null) {

       
        $file = pathinfo($filename, PATHINFO_FILENAME);
        $cachedfilename = $cache_dir . '/' . $file;
        
        if (is_null($className)) {
           $className = str_replace('.', '', $file);
        }
        

        $config = new ConfigCache($cachedfilename . '.php', $debug);

        if (!$config->isFresh() || $reload) {
            $container_builder = new ContainerBuilder();

            if (!file_exists($filename)) {
                throw new \InvalidArgumentException(
                sprintf("The module file '%s' does not exist.", $filename));
            }

            $loader = new XmlFileLoader($container_builder, new FileLocator(dirname($filename)));

            $loader->load(basename($filename));

            $extension = new Extension();
            $container_builder->registerExtension($extension);
            $container_builder->loadFromExtension($extension->getAlias());

            if (!empty($extensions)) {
                foreach ($extensions as $extension) {

                    $container_builder->registerExtension($extension);
                    $container_builder->loadFromExtension($extension->getAlias());
                }
            }

            $container_builder->compile();

            $dumper = new PhpDumper($container_builder);

            $config->write($dumper->dump(array('class' => $className)), $container_builder->getResources());
        }

        require $cachedfilename . '.php';
        return new $className;
    }

}
