<?php

namespace ExWife\Engine\Cms\Core\Model\Form;

use ExWife\Engine\Cms\Core\Service\CmsService;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ModelForm
 * @package ExWife\Engine\Cms\Model\Form
 */
class ModelForm extends AbstractType
{
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'model';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $connection = isset($options['connection']) ? $options['connection'] : null;

        $cmsMenuItems = [];
        $fullClass = UtilsService::getFullClassFromName('CmsMenuItem');
        $result = $fullClass::data($connection);
        foreach ($result as $itm) {
            $cmsMenuItems[$itm->title] = $itm->id;
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'Title:',
                'constraints' => [
                    new Assert\NotBlank()
                ],
            ])
            ->add('className', TextType::class, [
                'label' => 'Class name:',
                'constraints' => [
                    new Assert\NotBlank()
                ],
            ])
            ->add('modelCategory', ChoiceType::class, [
                'label' => 'Model category:',
                'choices' => [
                    'Customised' => 1,
                    'Built in' => 2,
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('listingType', ChoiceType::class, [
                'label' => 'Listing type:',
                'choices' => [
                    'Drag & Drop' => 1,
                    'Pagination' => 2,
                    'Tree' => 3,
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('pageSize', TextType::class, [
                'label' => 'Page size:',
            ])
            ->add('defaultSortBy', TextType::class, [
                'label' => 'Default sort by:'
            ])
            ->add('defaultOrderBy', ChoiceType::class, [
                'label' => 'Default order by:',
                'choices' => [
                    'DESC' => 'DESC',
                    'ASC' => 'ASC',
                ]
            ])
            ->add('accesses', ChoiceType::class, [
                'label' => 'Accesses:',
                'multiple' => 1,
                'choices' => $cmsMenuItems
            ])
            ->add('frontendUrl', TextType::class, [
                'label' => 'Frontend URL:',
            ])
            ->add('searchableInCms', CheckboxType::class, [
                'label' => 'Data is searchable in CMS',
            ])
            ->add('searchableInFrontend', CheckboxType::class, [
                'label' => 'Data is searchable in Frontend',
            ])
            ->add('enableVersioning', CheckboxType::class, [
                'label' => 'Enable versioning',
            ])
            ->add('columnsJson', TextareaType::class)
            ->add('_status', ChoiceType::class, [
                'expanded' => 1,
                'choices' => [
                    'Enabled' => 1,
                    'Disabled' => 0,
                ]
            ])
            ->add('_displayAdded', CheckboxType::class, [
                'label' => 'Add column "Added Date" in listing table',
            ])
            ->add('_displayModified', CheckboxType::class, [
                'label' => 'Add column "Last Modified Date" in listing table',
            ])
            ->add('_displayUser', CheckboxType::class, [
                'label' => 'Add column "Last Edited User" in listing table',
            ])
        ;

        $builder->get('accesses')
            ->addModelTransformer(new CallbackTransformer(
                function ($data) {
                    return gettype($data) == 'string' ? json_decode($data) : [];
                },
                function ($data) {
                    if (gettype($data) == 'array') {
                        $data = array_map(function ($itm) {
                            return $itm . '';
                        }, $data);
                        return json_encode($data);
                    }
                    return '[]';
                }
            ));

        $builder->get('searchableInCms')
            ->addModelTransformer(new CallbackTransformer(
                function ($data) {
                    return $data ? true : false;
                },
                function ($data) {
                    return $data ? 1 : 0;
                }
            ));

        $builder->get('searchableInFrontend')
            ->addModelTransformer(new CallbackTransformer(
                function ($data) {
                    return $data ? true : false;
                },
                function ($data) {
                    return $data ? 1 : 0;
                }
            ));

        $builder->get('enableVersioning')
            ->addModelTransformer(new CallbackTransformer(
                function ($data) {
                    return $data ? true : false;
                },
                function ($data) {
                    return $data ? 1 : 0;
                }
            ));

        $builder->get('_displayAdded')
            ->addModelTransformer(new CallbackTransformer(
                function ($data) {
                    return $data ? true : false;
                },
                function ($data) {
                    return $data ? 1 : 0;
                }
            ));

        $builder->get('_displayModified')
            ->addModelTransformer(new CallbackTransformer(
                function ($data) {
                    return $data ? true : false;
                },
                function ($data) {
                    return $data ? 1 : 0;
                }
            ));

        $builder->get('_displayUser')
            ->addModelTransformer(new CallbackTransformer(
                function ($data) {
                    return $data ? true : false;
                },
                function ($data) {
                    return $data ? 1 : 0;
                }
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'connection' => null,
        ]);
    }
}
