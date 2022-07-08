<?php

namespace SymfonyCMS\Engine\Cms\_Core\Model\Form;

use BlueM\Tree;
use Cocur\Slugify\Slugify;

use SymfonyCMS\Engine\Cms\_Core\Model\Form\Constraints\ConstraintUnique;
use SymfonyCMS\Engine\Cms\_Core\Model\Model;
use SymfonyCMS\Engine\Cms\_Core\Service\CmsService;
use SymfonyCMS\Engine\Cms\_Core\Service\ModelService;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use function Webmozart\Assert\Tests\StaticAnalysis\object;

class OrmProfileForm extends OrmForm
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('accessibleSections');
    }
}
