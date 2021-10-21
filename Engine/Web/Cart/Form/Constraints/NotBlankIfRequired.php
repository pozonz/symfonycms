<?php

namespace ExWife\Engine\Web\Cart\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class NotBlankIfRequired extends Constraint
{
	public $message = 'This value should not be blank.';
    public $callback = null;
    public $request = null;
}