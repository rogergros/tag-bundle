<?php

/**
 * This file is part of the Berny\TagBundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Berny\Bundle\TagBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collects all injectables and configures them like this:
 * Call "method" in each service tagged "tag", passing the "injectable" service as parameter.
 *
 * You can define an injectable with the "tag.injectable" tag. (by default)
 * Also you can redefine the method on a per service basis.
 * Mandatory parameters: method, tag
 * Optional parameters: -
 *
 * Example:
 *
 * services:
 *   dispatcher:
 *     class: Symfony\Component\EventDispatcher\EventDispatcher
 *     tags:
 *       - { name: "tag.injectable", tag: "dispatcher.aware", method: "setDispatcher" }
 *
 *   myservice:
 *     class: Acme\Bundle\Service\MyService
 *     tags:
 *       - { name: "dispatcher.aware" }
 *
 *   myservice:
 *     class: Acme\Bundle\Service\OtherService
 *     tags:
 *       - { name: "dispatcher.aware", "method": "setEventDispatcher" }
 *
 */
class TagInjectablePass implements CompilerPassInterface
{
    private $tag;

    /**
     * @param string $tag Name of the tag to mark services as tag injectors
     */
    public function __construct($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @param ContainerBuilder $container
     * @throws \InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds($this->tag) as $id => $tags) {
            $reference = new Reference($id);
            foreach ($tags as $attr) {
                $defaultMethod = $this->getAttribute($id, $attr, 'method');
                $tag = $this->getAttribute($id, $attr, 'tag');
                foreach ($container->findTaggedServiceIds($tag) as $serviceId => $serviceTags) {
                    $definition = $container->getDefinition($serviceId);
                    foreach ($serviceTags as $attr) {
                        $method = isset($attr['method']) ? $attr['method'] : $defaultMethod;
                        $definition->addMethodCall($method, array($reference));
                    }
                }
            }
        }
    }

    /**
     * Get attribute value
     *
     * @param string $id
     * @param array $attributes
     * @param string $attribute
     * @throws \InvalidArgumentException
     */
    protected function getAttribute($id, array $attributes, $attribute)
    {
        if (isset($attributes[$attribute])) {
            return $attributes[$attribute];
        }

        throw new \InvalidArgumentException(sprintf(
            'Service "%s" must define the "%s" attribute on "%s" tags.',
            $id, $attribute, $this->tag
        ));
    }
}
