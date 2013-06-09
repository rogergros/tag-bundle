<?php

/**
 * This file is part of the Berny\TagBundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Berny\Bundle\TagBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TagBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DependencyInjection\TagConsumerPass('tag.consumer'));
        $container->addCompilerPass(new DependencyInjection\TagInjectorPass('tag.injector'));
    }
}
