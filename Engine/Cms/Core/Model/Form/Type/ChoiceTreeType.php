<?php
namespace ExWife\Engine\Cms\Core\Model\Form\Type;

use MillenniumFalcon\Core\Nestable\Node;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChoiceTreeType extends AbstractType
{
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'choice_tree';
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['choices'] = $options['choices'];
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'compound' => false,
            'choices' => [],
            'placeholder' => "Choose options...",
        ]);
    }
}

