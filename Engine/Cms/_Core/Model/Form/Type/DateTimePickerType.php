<?php

namespace SymfonyCMS\Engine\Cms\_Core\Model\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DateTimePickerType extends AbstractType
{
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'datetimepicker';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => false
        ));
    }
}
