<?php

namespace AC\TranscodingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RegisterTranscoderTagsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('transcoder')) {
            return;
        }

        $transcoderDefinition = $container->getDefinition('transcoder');

        //register adapters/presets
        foreach (array('adapter','preset') as $tagType) {
            $tagName = 'transcoding.'.$tagType;
            $method = 'register'.ucfirst($tagType);

            //call registration method
            foreach ($container->findTaggedServiceIds($tagName) as $id => $attributes) {
                $transcoderDefinition->addMethodCall($method, array(new Reference($id)));
            }
        }

        $dispatcherDefinition = $container->getDefinition('event_dispatcher');

        //register event listeners
        foreach ($container->findTaggedServiceIds('transcoding.listener') as $id => $events) {
            foreach ($events as $event) {
                $priority = isset($event['priority']) ? $event['priority'] : 0;

                if (!isset($event['event'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "event" attribute on "transcoding.listener" tags.', $id));
                }

                if (!isset($event['method'])) {
                    $event['method'] = 'on'.preg_replace(array(
                        '/(?<=\b)[a-z]/ie',
                        '/[^a-z0-9]/i'
                    ), array('strtoupper("\\0")', ''), $event['event']);
                }

                $dispatcherDefinition->addMethodCall('addListenerService', array($event['event'], array($id, $event['method']), $priority));
            }
        }

        //register event subscribers
        foreach ($container->findTaggedServiceIds('transcoding.subscriber') as $id => $attributes) {
            // We must assume that the class value has been correcly filled, even if the service is created by a factory
            $class = $container->getDefinition($id)->getClass();

            $refClass = new \ReflectionClass($class);
            $interface = 'Symfony\Component\EventDispatcher\EventSubscriberInterface';
            if (!$refClass->implementsInterface($interface)) {
                throw new \InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interface));
            }

            $dispatcherDefinition->addMethodCall('addSubscriberService', array($id, $class));
        }
    }
}
