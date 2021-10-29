<?php
namespace ExWife\Engine\Cms\_Core\Model\Form\Type;

use \BlueM\Tree\Node;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChoiceTreeMultiType extends AbstractType
{
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'choice_tree_multi';
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

