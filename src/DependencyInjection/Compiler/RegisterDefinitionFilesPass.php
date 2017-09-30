<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\DependencyInjection\Compiler;

use Innmind\Filesystem\{
    Adapter\FilesystemAdapter,
    Directory,
    Exception\FileNotFound
};
use Innmind\Immutable\{
    Set,
    SetInterface
};
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Compiler\CompilerPassInterface
};

final class RegisterDefinitionFilesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $files = new Set('string');

        foreach ($bundles as $bundle => $class) {
            $files = $files->merge(
                $this->searchFiles($class)
            );
        }

        $container
            ->getDefinition('innmind_rest_server.definition.directories')
            ->addArgument($files->toPrimitive());
    }

    /**
     * @param string $class
     * @return SetInterface<string>
     */
    private function searchFiles(string $class): SetInterface
    {
        $files = new Set('string');

        try {
            $refl = new \ReflectionClass($class);
            $path = dirname($refl->getFileName());
            $config = (new FilesystemAdapter($path))
                ->get('Resources')
                ->get('config');
            $path .= '/Resources/config';

            if ($config->has('rest.yml')) {
                return $files->add($path.'/rest.yml');
            }

            $folder = $config->get('rest');

            foreach ($folder as $file) {
                if ($file instanceof Directory) {
                    continue;
                }

                $files = $files->add(
                    $path.'/rest/'.$file->name()
                );
            }
        } catch (FileNotFound $e) {
            //pass
        }

        return $files;
    }
}
