<?php
namespace SymfonyCMS\Engine\Cms\_Core\Model\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class ConstraintExist extends Constraint
{
	public $message = '"%string%" does not exist';
    public $connection = null;
    public $className = null;
    public $field = null;
    public $extraSql = null;
    public $mustBeEmail = null;
}