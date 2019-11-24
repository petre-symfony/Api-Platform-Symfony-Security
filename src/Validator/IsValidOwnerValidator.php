<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidOwnerValidator extends ConstraintValidator{
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(Security $security){
		$this->security = $security;
	}

	public function validate($value, Constraint $constraint) {
		/* @var $constraint \App\Validator\IsValidOwner */

		if (null === $value || '' === $value) {
			return;
		}

		// TODO: implement the validation here
		$user = $this->security->getUser();
		
		if(!$user instanceof User){
			$this->context->buildViolation($constraint->anonymousMessage)
				->addViolation();
			return;
		}

		if (!$value instanceof User) {
			throw new \InvalidArgumentException('@IsValidOwner constraint must be put on a property containing a User object');
		}

		if($value->getId() !== $user->getId()){
			$this->context->buildViolation($constraint->message)
				->addViolation();
		}
	}
}
