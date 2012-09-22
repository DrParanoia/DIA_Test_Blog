<?php

namespace DIA\TestBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class RegistrationType extends AbstractType {
	public function buildForm( FormBuilder $builder, array $options ) {
		$builder->add( 'user', new UserType() );
	}

	public function getName() {
		return 'registration';
	}
}

?>
