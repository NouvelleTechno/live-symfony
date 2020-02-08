<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UsersAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/inscription", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, UsersAuthenticator $authenticator, \Swift_Mailer $mailer): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // On génère le token d'activation
            $user->setActivationToken(md5(uniqid()));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            // On crée le message
            $message = (new \Swift_Message('Activation de votre compte'))
                // On attribue l'expéditeur
                ->setFrom('votre@adresse.fr')
                // On attribue le destinataire
                ->setTo($user->getEmail())
                // On crée le contenu
                ->setBody(
                    $this->renderView(
                        'emails/activation.html.twig', ['token' => $user->getActivationToken()]
                    ),
                    'text/html'
                )
            ;

            // On envoie l'e-mail
            $mailer->send($message);

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/activation/{token}", name="activation")
     */
    public function activation($token, UsersRepository $usersRepo){
        // On vérifie si un utilisateur a ce token
        $user = $usersRepo->findOneBy(['activation_token' => $token]);

        // Si aucun utilisateur n'existe avec ce token
        if(!$user){
            // Erreur 404
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
        }

        // On supprime le token
        $user->setActivationToken(null);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        // On envoie un message flash
        $this->addFlash('message', 'Vous avez bien activé votre compte');

        // On retoure à l'accueil
        return $this->redirectToRoute('accueil');
    }

    
}
