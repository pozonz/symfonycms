<?php
namespace SymfonyCMS\Engine\Cms\_Core\Model\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MultipleKeyValuePairType extends AbstractType
{
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'mkvp';
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

