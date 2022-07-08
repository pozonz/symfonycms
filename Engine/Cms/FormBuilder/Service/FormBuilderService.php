<?php

namespace SymfonyCMS\Engine\Cms\FormBuilder\Service;

use SymfonyCMS\Engine\Cms\_Core\Service\CmsService;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\File\Service\FileManagerService;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use phpDocumentor\Reflection\Types\Static_;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Twig\Extension\StringLoaderExtension;

class FormBuilderService
{
    const COUNTRY_SESSION_KEY = '__form_country';

    const FORM_BUILDER_DESCRIPTOR = FormBuilderDescriptor::class;

    /**
     * @var Connection
     */
    protected $_connection;

    /**
     * @var KernelInterface
     */
    protected $_kernel;

    /**
     * @var FormFactoryInterface
     */
    protected $_formFactory;

    /**
     * @var SessionInterface
     */
    protected $_session;

    /**
     * @var Environment
     */
    protected $_environment;

    /**
     * @var \Swift_Mailer
     */
    protected $_mailer;

    /**
     * @var FileManagerService
     */
    protected $_fileManagerService;

    /**
     * @var string
     */
    protected $_formBuilderClass;

    /**
     * Shop constructor.
     * @param Container $container
     */
    public function __construct(
        Connection $connection,
        KernelInterface $kernel,
        FormFactoryInterface $formFactory,
        SessionInterface $session,
        Environment $environment,
        \Swift_Mailer $mailer,
        FileManagerService $fileManagerService
    ) {
        $this->_connection = $connection;
        $this->_kernel = $kernel;
        $this->_formFactory = $formFactory;
        $this->_session = $session;
        $this->_environment = $environment;
        $this->_mailer = $mailer;
        $this->_fileManagerService = $fileManagerService;
        $this->_formBuilderClass = static::FORM_BUILDER_DESCRIPTOR;
        $this->_theme = CmsService::getTheme();
    }

    /**
     * @param $code
     * @param array $options
     * @return mixed
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getForm($code, $options = [])
    {
        $request = $options['request'] ?? Request::createFromGlobals();

        $pdo = $this->_connection;
        $baseUrl = $request->getSchemeAndHttpHost();
        $fullUri = $request->getUri();
        $ip = $request->getClientIp();

        $fullClass = UtilsService::getFullClassFromName('FormBuilder');
        $formBuilder = $fullClass::getByField($this->_connection, 'code', $code);
        if (is_null($formBuilder)) {
            throw new NotFoundHttpException();
        }
        $formBuilder->sent = false;

        /** @var FormFactory $formFactory */
        $formFactory = $this->_formFactory;

        /** @var \Symfony\Component\Form\Form $form */
        $form = $formFactory->createNamedBuilder(
            'form_' . $formBuilder->code,
            $this->_formBuilderClass,
            null,
            [
                'formBuilder' => $formBuilder,
            ]
        )->getForm();

        $form->handleRequest($request);
        $formBuilder->form = $form->createView();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $data = (array)$form->getData();

                $assetFullClass = UtilsService::getFullClassFromName('Asset');
                $resultWithLabel = [];
                $resultWithField = [];

                foreach (json_decode($formBuilder->formFields) as $field) {
                    if ($field->widget == 'submit') {
                        continue;
                    }

                    $value = $data[$field->id];
                    if (gettype($value) == 'array') {
                        $value = implode(', ', $value);

                    } elseif (gettype($value) == 'object' && get_class($value) == 'Symfony\Component\HttpFoundation\File\UploadedFile') {

                        $parentName = 'Submitted form uploads';
                        $parent = $assetFullClass::data($this->_connection, [
                            'whereSql' => 'm.title = ? AND (m.parentId IS NULL OR m.parentId = 0)',
                            'params' => [$parentName],
                            'limit' => 1,
                            'oneOrNull' => 1,
                        ]);
                        if (!$parent) {
                            $parent = new $assetFullClass($this->_connection);
                            $parent->title = $parentName;
                            $parent->isFolder = 1;
                            $parent->parentId = 0;
                            $parent->save();
                        }
                        $folder = $assetFullClass::data($this->_connection, [
                            'whereSql' => 'm.title = ? AND m.parentId = ?',
                            'params' => [$formBuilder->title, $parent->id],
                            'limit' => 1,
                            'oneOrNull' => 1,
                        ]);
                        if (!$folder) {
                            $folder = new $assetFullClass($this->_connection);
                            $folder->title = $formBuilder->title;
                            $folder->isFolder = 1;
                            $folder->parentId = $parent->id;
                            $folder->rank = time();
                            $folder->save();
                        }

                        $originalName = $value->getClientOriginalName();
                        $asset = new $assetFullClass($this->_connection);
                        $asset->title = $originalName;
                        $asset->isFolder = 0;
                        $asset->parentId = $folder->id;
                        $asset->rank = time();
                        $asset->save();

                        $this->_fileManagerService->processUploadedFileWithAsset($value, $asset);
                        $value = $baseUrl . "/downloads/assets/{$asset->code}/{$asset->fileName}";
                    }

                    $resultWithLabel[] = [$field->label, static::getFormData($value, $field->widget), $field->widget];
                    $resultWithField[$field->id] = static::getFormData($value, $field->widget);
                }

                $this->beforeSend($formBuilder, $resultWithLabel, $data);

                $countryInfo = $this->_session->get(static::COUNTRY_SESSION_KEY);

                $fullClass = UtilsService::getFullClassFromName('FormSubmission');
                $submission = new $fullClass($this->_connection);
                $submission->date = date('Y-m-d H:i:s');
                $submission->fromAddress = $formBuilder->fromAddress;
                $submission->recipients = $formBuilder->recipients;
                $submission->content = json_encode($resultWithLabel);
                $submission->contentWithField = json_encode($resultWithField);
                $submission->emailStatus = 0;
                $submission->formDescriptorId = $formBuilder->id;
                $submission->formName = $formBuilder->title;
                $submission->url = $fullUri;
                $submission->ip = $ip;
                $submission->country = json_encode($countryInfo);
                $submission->save();

                $code = UtilsService::generateHex(4) . '-' . $submission->id;
                $submission->title = "{$formBuilder->formName} #{$code}";
                $submission->uniqueId = $code;
                $submission->save();

                $formBuilder->formSubmission = $submission;

                $recipients = array_filter(array_map(function ($itm) {
                    $itm = trim($itm);
                    return filter_var($itm, FILTER_VALIDATE_EMAIL) ? $itm : null;
                }, explode(',', $formBuilder->recipients)));

                if (count($recipients)) {
                    $messageBody = $this->_environment->render("cms/{$this->_theme}/email/form_submission.twig", [
                        '_formBuilder' => $formBuilder,
                        '_submission' => $submission,
                    ]);

                    $message = (new \Swift_Message())
                        ->setSubject("{$formBuilder->title} {$submission->title}")
                        ->setFrom([$formBuilder->fromAddress])
                        ->setTo($recipients)
                        ->setBcc(array_filter(explode(',', $_ENV['EMAIL_BCC_ORDER'])))
                        ->setBody(
                            $messageBody, 'text/html'
                        );

                    if (isset($data['email']) && $data['email'] && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                        $message->setReplyTo([$data['email']]);
                    }

                    $formBuilder->sent = $this->_mailer->send($message);

                    $submission->emailStatus = $formBuilder->sent ? 1 : 2;
                    $submission->emailRequest = $messageBody;
                    $submission->emailResponse = $formBuilder->sent;
                    $submission->save();

                    
                    if ($formBuilder->sendThankYouEmail && isset($data['email']) && $data['email'] && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                        $this->_environment->getExtension(StringLoaderExtension::class);
                        $messageBody = $this->_environment->render("cms/{$this->_theme}/email/form_submission_thankyou.twig", array_merge([
                            '_formBuilder' => $formBuilder,
                            '_submission' => $submission,
                        ], (array)json_decode($submission->contentWithField ?: '{}')));

                        $message = (new \Swift_Message())
                            ->setSubject("{$formBuilder->thankYouEmailSubject} {$submission->title}")
                            ->setFrom([$formBuilder->fromAddress])
                            ->setTo($data['email'])
                            ->setBcc(array_filter(explode(',', $_ENV['EMAIL_BCC_ORDER'])))
                            ->setBody(
                                $messageBody, 'text/html'
                            );
                        $this->_mailer->send($message);
                    }
                }

                $formBuilder->sent = 1;
                $this->afterSend($formBuilder, $resultWithLabel, $data);
            }
        }

        return $formBuilder;
    }

    /**
     * @param $formBuilder
     * @param $resultWithLabel
     * @param $data
     */
    public function beforeSend($formBuilder, &$resultWithLabel, $data) {}

    /**
     * @param $formBuilder
     * @param $resultWithLabel
     * @param $data
     */
    public function afterSend($formBuilder, &$resultWithLabel, $data) {}

    /**
     * @param $value
     * @param $widget
     * @return string
     */
    public function getFormData($value, $widget)
    {
        if ($widget == '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\TextareaType') {
            return nl2br($value);
        }
        return strip_tags($value);
    }

    /**
     * @return array
     */
    public function getFormFieldWidgets()
    {
        $widgets =[
            'Date' => '\\SymfonyCMS\\Engine\\Cms\\FormBuilder\\Form\\Type\\DateType',
            'File' => '\\SymfonyCMS\\Engine\\Cms\\FormBuilder\\Form\\Type\\FileType',
            'Dropdown' => '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType',
            'Checkboxes' => '\\SymfonyCMS\\Engine\Cms\\FormBuilder\\Form\\Type\\CheckboxesType',
            'Radio buttons' => '\\SymfonyCMS\Engine\\Cms\\FormBuilder\\Form\\Type\\RadioButtonsType',
            'Checkbox' => '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType',
            'Email' => '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\EmailType',
            'Hidden' => '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\HiddenType',
            'Text' => '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType',
            'Textarea' => '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\TextareaType',
            'Repeated' => '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\RepeatedType',
            'Submit' => '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\SubmitType',
        ];
        ksort($widgets);
        return array_flip($widgets);
    }

    /**
     * @return string[]
     */
    public function getFormFieldWidgetsNeedQuery()
    {
        return [
            '\\Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType',
            '\\SymfonyCMS\\Engine\Cms\\FormBuilder\\Form\\Type\\CheckboxesType',
            '\\SymfonyCMS\Engine\\Cms\\FormBuilder\\Form\\Type\\RadioButtonsType',
        ];
    }

    /**
     * @param Request $request
     * @return array|null[]
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    static public function ip_info(Request $request)
    {
        $ip = $_ENV['TEST_CLIENT_IP'] ?: $request->getClientIp();
        if ($_ENV['GEOIP_DB_PATH']) {
            $geoDbPath = $_ENV['GEOIP_DB_PATH'];
            if (file_exists($geoDbPath)) {
                $geoipReader = new Reader($geoDbPath);
                try {
                    $geoIpCountry = $geoipReader->country($ip);
                    return [
                        'name' => $geoIpCountry->country->name,
                        'country_code' => $geoIpCountry->country->isoCode,
                        'geonameId' => $geoIpCountry->country->geonameId
                    ];


                } catch (AddressNotFoundException $addressNotFoundException){}
            }
        }
        return null;
    }
}
