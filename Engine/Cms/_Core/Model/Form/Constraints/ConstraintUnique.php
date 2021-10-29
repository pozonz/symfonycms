<?php
namespace ExWife\Engine\Cms\_Core\Model\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ConstraintUnique extends Constraint
{
	public $message = '"%string%" has been used';
	public $orm = null;
    public $field = null;
    public $extraSql = null;
    public $joins = null;
}