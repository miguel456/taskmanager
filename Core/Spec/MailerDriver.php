<?php

namespace App\Core\Spec;

/**
 * "Contrato" do "driver" do Mailer, definindo os contornos gerais do comportamento do Mailer.
 */
interface MailerDriver
{
    /**
     * Permite que o driver se descreva si próprio, definindo a arquitetura que suporta.
     * @return mixed
     */
    public function supports();
}