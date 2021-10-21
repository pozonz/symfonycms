<?php

namespace ExWife\Engine\Cms\FormBuilder\Service;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Connection;


use ExWife\Engine\Cms\Core\ORM\FormBuilder;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use ExWife\Engine\Cms\FormBuilder\Form\Constraints\ConstraintRobot;
use ExWife\Engine\Cms\FormBuilder\Form\Type\RobotType;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class FormBuilderDescriptor extends AbstractType
{
    /**
     * @var Connection
     */
    protected $_connection;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * FormBuilderDescriptor constructor.
     * @param Connection $connection
     * @param SessionInterface $session
     * @param KernelInterface $kernel
     */
    public function __construct(Connection $connection, SessionInterface $session, KernelInterface $kernel)
    {
        $this->_connection = $connection;
        $this->_session = $session;
        $this->_kernel = $kernel;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var FormBuilder $formBuilder */
        $formBuilder = $options['formBuilder'];
        /** @var Request $request */
        $request = $options['request'] ?? Request::createFromGlobals();

        $formFields = json_decode($formBuilder->formFields);
        foreach ($formFields as $key => $field) {
            $widgetClassName = $field->widget;
            $builder->add($field->id, $widgetClassName, $this->getOptionsForField($field));
        }

        $this->buildEventListeners($formBuilder, $builder);

        $countryInfo = $this->_session->get(FormBuilderService::COUNTRY_SESSION_KEY);
        if (!$countryInfo) {
            $countryInfo = FormBuilderService::ip_info($request);
            $countryInfo = $countryInfo ?: [];
            $this->_session->set(FormBuilderService::COUNTRY_SESSION_KEY, $countryInfo);
        }

        if ($formBuilder->antispam) {
            $safeCountries = getenv('SAFE_COUNTRIES') ?: 'NZ,AU';
            if (!isset($countryInfo['country_code']) || !in_array($countryInfo['country_code'], explode(',', $safeCountries))) {
                $builder->add('robot', RobotType::class, array(
                    "mapped" => false,
                    'label' => '',
                    'constraints' => array(
                        new ConstraintRobot(),
                    )
                ));
            }
        }

        $this->formBuilder = $formBuilder;
    }

    /**
     * @param $formBuilder
     * @param $builder
     */
    public function buildEventListeners(&$formBuilder, &$builder)
    {
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form_' . $this->formBuilder->getCode();
    }

    /**
     * @param $field
     * @return array
     */
    public function getOptionsForField($field)
    {
        $options = [
            'required' => $field->required ? true : false,
            'label' => $field->label,
            'attr' => [
//                'placeholder' => preg_replace("/[^a-zA-Z0-9\ ]+/", "", $field->label),
            ],
        ];

        switch ($field->widget) {
            case '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType':
                $options['choices'] = $this->getChoicesForField($field);
                $options['multiple'] = false;
                $options['expanded'] = false;
                $options['empty_data'] = null;
                $options['placeholder'] = 'Choose...';
                break;
            case '\\ExWife\\Engine\\Cms\\FormBuilder\\Form\\Type\\RadioButtonsType':
                $options['choices'] = $this->getChoicesForField($field);
                $options['multiple'] = false;
                $options['expanded'] = true;
                $options['empty_data'] = null;
                $options['placeholder'] = false;
                break;
            case '\\ExWife\\Engine\\Cms\\FormBuilder\\Form\\Type\\CheckboxesType':
                $options['choices'] = $this->getChoicesForField($field);
                $options['multiple'] = true;
                $options['expanded'] = true;
                $options['empty_data'] = null;
                $options['placeholder'] = false;
                break;
            case 'repeated':
                $options['type'] = 'password';
                $options['invalid_message'] = 'The password fields must match.';
                $options['options'] = array('attr' => array('class' => 'password-field'));
                $options['first_options'] = array('label' => 'Password (8 characters or more):', 'attr' => array('placeholder' => 'Enter Password'));
                $options['second_options'] = array('label' => 'Repeat Password:', 'attr' => array('placeholder' => 'Confirm Password'));
                break;
        }

        $constraints = $this->getValidationForField($field);
        if (count($constraints) > 0) {
            $options['constraints'] = $constraints;
        }

        return $options;
    }

    /**
     * @param $field
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getChoicesForField($field)
    {
        if (!isset($field->optionType) || $field->optionType == 1) {
            $slugify = new Slugify(['trim' => false]);
            preg_match('/\bfrom\b\s*(\w+)/i', $field->sql, $matches);
            if (count($matches) == 2) {
                if (substr($matches[1], 0, 1) == '_') {
                    $tablename = strtolower($matches[1]);
                } else {
                    $tablename = $slugify->slugify($matches[1]);
                }

                $field->sql = str_replace($matches[0], "FROM $tablename", $field->sql);
            }

            $pdo = $this->_connection;
            $stmt = $pdo->executeQuery($field->sql);
            $stmt->execute();
            $choices = [];
            foreach ($stmt->fetchAll() as $key => $val) {
                $choices[$val['value']] = $val['key'];
            }
            return $choices;
        } else {
            $options = $field->options ?? [];
            $choices = [];
            foreach ($options as $idx => $itm) {
                $choices[$itm->val] = $itm->key;
            }
            return $choices;
        }
        return [];
    }

    /**
     * @param $field
     * @return array
     */
    public function getValidationForField($field)
    {
        $validations = [];

        if ($field->widget == '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\EmailType') {
            $validations[] = new Assert\Email([
                'mode' => 'html5',
            ]);
        }

        if ($field->widget == '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\RepeatedType') {
            $validations[] = new Assert\Length(array(
                'min' => 6,
                'max' => 128,
            ));
        }

        if (isset($field->required) && $field->required) {
            $notBlank = new Assert\NotBlank();
            if (isset($field->errorMessage) && $field->errorMessage) {
                $notBlank->message = $field->errorMessage;
            }
            $validations[] = $notBlank;
        }

        return $validations;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('formDescriptor', null);
        $resolver->setRequired(['formBuilder']);
    }
}
