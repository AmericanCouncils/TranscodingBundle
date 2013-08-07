<?php
namespace AC\TranscodingBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class ACTranscodingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        //include ffmpeg?
        if ($config['ffmpeg']['enabled']) {
            $loader->load('services.ffmpeg.yml');
        }

        //include handbrake?
        if ($config['handbrake']['enabled']) {
            $loader->load('services.handbrake.yml');
        }
    }
}
