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
        $config = $this->processConfiguration(
            new Configuration,
            $configs
        );

        $this->registerTypes($config['types'], $container);
    }

    /**
     * @return void
     */
    private function registerTypes(array $types, ContainerBuilder $container)
    {
        $definition = $container->getDefinition(
            'innmind_rest_server.definition.types'
        );

        foreach ($types as $type) {
            $definition->addMethodCall(
                'register',
                [$type]
            );
        }
    }
}
