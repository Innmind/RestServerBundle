<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\DependencyInjection;

use Symfony\Component\{
    HttpKernel\DependencyInjection\Extension,
    DependencyInjection\ContainerBuilder,
    DependencyInjection\Loader,
    Config\FileLocator
};

final class InnmindRestServerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');
    }
}
