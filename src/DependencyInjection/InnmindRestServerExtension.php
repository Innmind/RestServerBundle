<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\DependencyInjection;

use Innmind\Rest\Server\Format\{
    Format,
    MediaType
};
use Innmind\Immutable\{
    Map,
    Set
};
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
            ->registerContentTypeFormats($config['content_type'], $container);
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
        return $this->registerFormats(
            'innmind_rest_server.formats.accept',
            $formats,
            $container
        );
    }

    private function registerContentTypeFormats(
        array $formats,
        ContainerBuilder $container
    ): self {
        return $this->registerFormats(
            'innmind_rest_server.formats.content_type',
            $formats,
            $container
        );
    }

    private function registerFormats(
        string $service,
        array $formats,
        ContainerBuilder $container
    ): self {
        $map = new Map('string', Format::class);

        foreach ($formats as $format => $formatConfig) {
            $mediaTypes = new Set(MediaType::class);

            foreach ($formatConfig['media_types'] as $mediaType => $priority) {
                $mediaTypes = $mediaTypes->add(
                    new MediaType($mediaType, $priority)
                );
            }

            $map = $map->put(
                $format,
                new Format($format, $mediaTypes, $formatConfig['priority'])
            );
        }

        $container
            ->getDefinition($service)
            ->addArgument($map);

        return $this;
    }
}
