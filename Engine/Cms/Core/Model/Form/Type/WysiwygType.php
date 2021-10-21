<?php

namespace ExWife\Engine\Cms\Core\Model\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WysiwygType extends AbstractType
{
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'wysiwyg';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => false,
        ));
    }
}
