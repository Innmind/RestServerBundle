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
            ->addCompilerPass(new RegisterHeaderBuildersPass(Action::list()))
            ->addCompilerPass(new RegisterHeaderBuildersPass(Action::get()))
            ->addCompilerPass(new RegisterHeaderBuildersPass(Action::create()))
            ->addCompilerPass(new RegisterHeaderBuildersPass(Action::update()))
            ->addCompilerPass(new RegisterHeaderBuildersPass(Action::remove()))
            ->addCompilerPass(new RegisterHeaderBuildersPass(Action::link()))
            ->addCompilerPass(new RegisterHeaderBuildersPass(Action::unlink()));
    }
}
