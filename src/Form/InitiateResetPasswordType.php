<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WizaplaceFrontBundle\Entity\InitiateResetPasswordCommand;

class InitiateResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => InitiateResetPasswordCommand::class,
            'csrf_protection' => true,
            'csrf_field_name' => 'csrf_token',
            // a unique key to help generate the secret token
            'csrf_token_id'   => 'password_token',
        ));
    }
}
