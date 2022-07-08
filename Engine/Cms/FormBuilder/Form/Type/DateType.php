<?php

namespace SymfonyCMS\Engine\Cms\FormBuilder\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DateType extends AbstractType
{
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'date_picker';
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
