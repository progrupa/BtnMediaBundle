<?php

namespace Btn\MediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BtnMediaExtension extends Extension implements PrependExtensionInterface
{
    private $resourceDir = '/../Resources/config';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('btn_media.media.class', $config['media']['class']);
        $container->setParameter('btn_media.media.allowed_extensions', $config['media']['allowed_extensions']);
        $container->setParameter('btn_media.media_category.class', $config['media_category']['class']);
        $container->setParameter('btn_media.media_category.template', $config['media_category']['template']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . $this->resourceDir));
        $loader->load('services.yml');
        $loader->load('forms.yml');
        $loader->load('menus.yml');
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('liip_imagine')) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . $this->resourceDir));
            $loader->load('liip_imagine.yml');
        }
    }
}
