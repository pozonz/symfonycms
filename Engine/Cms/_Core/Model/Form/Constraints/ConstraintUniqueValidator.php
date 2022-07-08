<?php
namespace SymfonyCMS\Engine\Cms\_Core\Model\Form\Constraints;

use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use MillenniumFalcon\Core\Service\ModelService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ConstraintUniqueValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $orm = $constraint->orm;
        $field = $constraint->field;
        $extraSql = $constraint->extraSql;
        $joins = $constraint->joins;

        $fullClass = UtilsService::getFullClassFromName(UtilsService::basename(get_class($orm)));
        if ($orm->_versionOrmId) {
            $data = $fullClass::data($orm->_connection, array(
                'joins' => $joins,
                'whereSql' => "(m.$field = ? AND m.id != ? AND m._versionOrmId IS NULL)" . ($extraSql ? " AND ($extraSql)" : ''),
                'params' => array($value, $orm->_versionOrmId),
                'debug' => 0,
            ));
        } else if ($orm->id) {
            $data = $fullClass::data($orm->_connection, array(
                'joins' => $joins,
                'whereSql' => "(m.$field = ? AND m.id != ? AND m._versionOrmId IS NULL)" . ($extraSql ? " AND ($extraSql)" : ''),
                'params' => array($value, $orm->id),
                'debug' => 0,
            ));
        } else {
            $data = $fullClass::data($orm->_connection, array(
                'joins' => $joins,
                'whereSql' => "(m.$field = ? AND m._versionOrmId IS NULL)" . ($extraSql ? " AND ($extraSql)" : ''),
                'params' => array($value),
                'debug' => 0,
            ));
        }
        if (count($data)) {
            $this->context->addViolation(
                $constraint->message,
                array('%string%' => $value)
            );
        }
    }
}