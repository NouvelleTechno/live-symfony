<?php

namespace App\Notifications;

// On importe les classes nécessaires à l'envoi d'e-mail et à twig

use App\Entity\Users;
use Swift_Message;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ActivationCompteNotification
{
    /**
     * Propriété contenant le module d'envoi de mails
     * 
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * Propriété contenant l'environnement Twig
     *
     * @var Environment
     */
    private $renderer;

    public function __construct(\Swift_Mailer $mailer, Environment $renderer)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    /**
     * Méthode de notification (envoi de mail)
     *
     * @return void
     */
    public function notify(Users $user)
    {
        // On construit le mail
        $message = (new Swift_Message('Mon blog - Activation de votre compte'))
            // Expéditeur
            ->setFrom('no-reply@monblog.fr')
            // Destinataire
            ->setTo($user->getEmail())
            // Corps du message
            ->setBody(
                $this->renderer->render(
                    'emails/activation.html.twig',
                    ['token' => $user->getActivationToken()]
                ),
                'text/html'
            );

        // On envoie le mail
        $this->mailer->send($message);
        
    }
}