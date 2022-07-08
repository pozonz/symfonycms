<?php

namespace SymfonyCMS\Engine\Cms\FormBuilder\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RobotType extends AbstractType
{

    public function getBlockPrefix()
    {
        return 'robot';
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'compound' => false,
        ));
    }
}