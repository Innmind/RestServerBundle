<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle;

use Innmind\Rest\ServerBundle\DependencyInjection\Compiler\{
    RegisterDefinitionFilesPass,
    RegisterGatewaysPass,
    RegisterHttpHeaderFactoriesPass
};
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
            ->addCompilerPass(new RegisterHttpHeaderFactoriesPass);
    }
}
