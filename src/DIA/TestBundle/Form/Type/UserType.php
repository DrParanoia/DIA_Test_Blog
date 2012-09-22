<?php
namespace DIA\TestBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserType extends AbstractType {
    public function buildForm( FormBuilder $builder, array $options ) {
        $builder->add( 
            'password', 'repeated', 
            array(
                'first_name' => 'password',
                'second_name' => 'confirm',
                'type' => 'password',
                'invalid_message' => "Passwords don't match"               
            )
        )
        ->add('username', 'text')
        ->add('first_name', 'text', array('required' => false) )
        ->add('last_name', 'text', array('required' => false) );
    }

    public function getDefaultOptions( array $options ) {
        return array( 'data_class' => 'DIA\TestBundle\Entity\User' );
    }

    public function getName() {
        return 'user';
    }
}
?>
