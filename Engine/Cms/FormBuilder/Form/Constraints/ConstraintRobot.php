<?php

namespace SymfonyCMS\Engine\Cms\FormBuilder\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ConstraintRobot extends Constraint
{
	public $message = 'Please try again';
}