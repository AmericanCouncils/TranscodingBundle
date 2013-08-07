<?php

namespace AC\TranscodingBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use AC\TranscodingBundle\DependencyInjection\RegisterTranscoderTagsPass;

class ACTranscodingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterTranscoderTagsPass());
    }

}
