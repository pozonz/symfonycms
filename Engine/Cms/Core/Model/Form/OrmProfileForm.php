<?php

namespace ExWife\Engine\Cms\Core\Model\Form;

use BlueM\Tree;
use Cocur\Slugify\Slugify;

use ExWife\Engine\Cms\Core\Model\Form\Constraints\ConstraintUnique;
use ExWife\Engine\Cms\Core\Model\Model;
use ExWife\Engine\Cms\Core\Service\CmsService;
use ExWife\Engine\Cms\Core\Service\ModelService;
use ExWife\Engine\Cms\Core\Service\UtilsService;
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
