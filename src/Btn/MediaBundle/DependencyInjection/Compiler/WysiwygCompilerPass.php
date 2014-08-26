<?php

namespace Btn\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class WysiwygCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $wysiwygId = 'btn_admin.form.type.wysiwyg';

        if (!$container->hasDefinition($wysiwygId)) {
            return;
        }

        $wysiwyg = $container->getDefinition($wysiwygId);
        $wysiwyg->addMethodCall('setFilebrowserBrowseRoute', array('btn_media_mediacontrol_modal'));
    }
}
