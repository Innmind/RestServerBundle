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
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');
        $config = $this->processConfiguration(
            new Configuration,
            $configs
        );

        $this
            ->registerTypes($config['types'], $container)
            ->registerAcceptFormats($config['accept'], $container)
            ->registerContentTypeFormats($config['content_type'], $container)
            ->configureRangeExtractor($config['range_extractor'], $container)
            ->configureSpecificationBuilder(
                $config['specification_builder'],
                $container
            )
            ->configureHeaderBuilders(
                $config['response']['header_builders'],
                $container
            );
    }

    private function registerTypes(array $types, ContainerBuilder $container): self
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

        return $this;
    }

    private function registerAcceptFormats(
        array $formats,
        ContainerBuilder $container
    ): self {
        $container
            ->getDefinition('innmind_rest_server.formats.accept')
            ->replaceArgument(0, $formats);

        return $this;
    }

    private function registerContentTypeFormats(
        array $formats,
        ContainerBuilder $container
    ): self {
        $container
            ->getDefinition('innmind_rest_server.formats.content_type')
            ->replaceArgument(0, $formats);

        return $this;
    }

    private function configureRangeExtractor(
        string $service,
        ContainerBuilder $container
    ): self {
        $container->setAlias(
            'innmind_rest_server.range_extractor',
            $service
        );

        return $this;
    }

    private function configureSpecificationBuilder(
        string $service,
        ContainerBuilder $container
    ): self {
        $container->setAlias(
            'innmind_rest_server.specification_builder',
            $service
        );

        return $this;
    }

    private function configureHeaderBuilders(
        array $builders,
        ContainerBuilder $container
    ): self {
        foreach ($builders as $action => $value) {
            $container->setAlias(
                'innmind_rest_server.response.header_builder.'.$action,
                $value
            );
        }

        return $this;
    }
}
