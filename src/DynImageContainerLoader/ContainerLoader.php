<?php

namespace DynImageContainerLoader;


use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class ContainerLoader {

    static public function load($filename, $cache_dir, $debug = false, $extensions = array()) {


        $file = pathinfo($filename, PATHINFO_FILENAME);
        $cachedfilename = $cache_dir . '/' . $file;
        $className = str_replace('.', '', $file);

        $config = new ConfigCache($cachedfilename . '.php', $debug);

        if (!$config->isFresh()) {
            $container_builder = new ContainerBuilder();

            if (!file_exists($filename)) {
                throw new \InvalidArgumentException(
                sprintf("The module file '%s' does not exist.", $filename));
            }

            $loader = new XmlFileLoader($container_builder, new FileLocator(dirname($filename)));

            $loader->load(basename($filename));
            /**/
            //permet d'ajouter imageManager en tant que service dans le module
            $extension = new Extension();
            $container_builder->registerExtension($extension);
            $container_builder->loadFromExtension($extension->getAlias());
            //$container->addCompilerPass(new CompilerPass, PassConfig::TYPE_BEFORE_OPTIMIZATION);
            /**/

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
