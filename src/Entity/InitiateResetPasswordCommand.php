<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class InitiateResetPasswordCommand
{
    /**
     * @Assert\Email()
     * @Assert\NotBlank()
     */
    private $email;

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }
}
