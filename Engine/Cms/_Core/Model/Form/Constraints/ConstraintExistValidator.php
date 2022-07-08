<?php
namespace SymfonyCMS\Engine\Cms\_Core\Model\Form\Constraints;

use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ConstraintExistValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	{
        $connection = $constraint->connection;
        $className = $constraint->className;
        $field = $constraint->field;
        $extraSql = $constraint->extraSql;
        $mustBeEmail = $constraint->mustBeEmail;

        if ($value) {
            if (!$mustBeEmail || filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $fullClass = UtilsService::getFullClassFromName($className);
                $orm = $fullClass::data($connection, array(
                    'whereSql' => "(m.$field = ?)" . ($extraSql ? " AND ($extraSql)" : ''),
                    'params' => array($value),
                    'limit' => 1,
                    'oneOrNull' => 1,
                ));
                if (!$orm) {
                    $this->context->addViolation(
                        $constraint->message,
                        array('%string%' => $value)
                    );
                }
            }
        }
	}
}