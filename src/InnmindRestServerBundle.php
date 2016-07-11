<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle;

use Innmind\Rest\ServerBundle\DependencyInjection\Compiler\{
    RegisterDefinitionFilesPass,
    RegisterGatewaysPass,
    RegisterHttpHeaderFactoriesPass,
    RegisterRequestVerifiersPass,
    RegisterRangeExtractorsPass,
    RegisterHeaderBuildersPass
};
use Innmind\Rest\Server\Action;
use Symfony\Component\{
    HttpKernel\Bundle\Bundle,
    DependencyInjection\ContainerBuilder
};

final class InnmindRestServerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new RegisterDefinitionFilesPass)
            ->addCompilerPass(new RegisterGatewaysPass)
            ->addCompilerPass(new RegisterHttpHeaderFactoriesPass)
            ->addCompilerPass(new RegisterRequestVerifiersPass)
            ->addCompilerPass(new RegisterRangeExtractorsPass)
            ->addCompilerPass(new RegisterHeaderBuildersPass(Action::LIST))
            ->addCompilerPass(new RegisterHeaderBuildersPass(Action::GET))
            ->addCompilerPass(new RegisterHeaderBuildersPass(Action::CREATE))
            ->addCompilerPass(new RegisterHeaderBuildersPass(Action::UPDATE));
    }
}
