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

class OrmForm extends AbstractType
{
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'orm';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('_uniqid', HiddenType::class);

        /** @var Model $model */
        $model = isset($options['model']) ? $options['model'] : null;
        $orm = isset($options['orm']) ? $options['orm'] : null;
        $connection = isset($options['connection']) ? $options['connection'] : null;
        /** @var CmsService $cmsService */
        $cmsService = isset($options['cmsService']) ? $options['cmsService'] : null;

        $columnsJson = $model->objColumnsJson();
        foreach ($columnsJson as $itm) {
            if ($itm->widget == 'Checkbox') {
                $field = $itm->field;
                $orm->$field = $orm->$field ? true : false;
            }

            $modelColumnWidgets = ModelService::getModelColumnWidgets();
            $widget = $modelColumnWidgets[$itm->widget] ?? null;
            if ($widget) {
                $opts = $this->getOpts($connection, $itm, $orm, $model, $cmsService);
                $builder->add($itm->field, $widget, $opts);
            }
        }

        $builder->add('__draftName', TextType::class, [
            'mapped' => false
        ]);

        $builder->add('_status', ChoiceType::class, [
            'expanded' => 1,
            'choices' => [
                'Enabled' => 1,
                'Disabled' => 0,
            ]
        ]);
    }

    /**
     * @param $column
     * @return array
     */
    protected function getOpts($connection, $column, $orm, $model, CmsService $cmsService)
    {
        $opts = [
            'label' => $column->label,
        ];

        if (!isset($opts['constraints']) || gettype($opts['constraints']) != 'array') {
            $opts['constraints'] = [];
        }

        if ($column->required == 1) {
            $opts['constraints'][] = new Assert\NotBlank();
        }

        if ($column->unique == 1) {
            $opts['constraints'][] = new ConstraintUnique([
                'orm' => $orm,
                'field' => $column->field,
            ]);
        }

        if (in_array($column->widget, ModelService::getRelationalWidgets())) {
            $opts['choices'] = ModelService::getChoicesByWidget($column->widget, $connection, $column->sqlQuery, $cmsService);
        }

        switch ($column->widget) {
            case 'Choice':
                $opts['placeholder'] = 'Choose an option...';
                break;
            case 'Content blocks':
                $opts['model'] = $model;
                break;
            default:
                break;
        }


        return $opts;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'model' => null,
            'orm' => null,
            'connection' => null,
            'cmsService' => null,
        ]);
    }
}
