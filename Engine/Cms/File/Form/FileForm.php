<?php

namespace ExWife\Engine\Cms\File\Form;

use Cocur\Slugify\Slugify;
use ExWife\Engine\Cms\_Core\Model\Form\OrmForm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FileForm extends OrmForm
{

    public function getBlockPrefix()
    {
        return 'orm';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('file', FileType::class, [
                'mapped' => false,
            ]);
    }
}
