<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Users;
use App\Repository\ArticlesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/api", name="api_")
 */
class APIController extends AbstractController
{
    /**
     * @Route("/articles/liste", name="liste", methods={"GET"})
     */
    public function liste(ArticlesRepository $articlesRepo)
    {
        // On récupère la liste des articles
        $articles = $articlesRepo->apiFindAll();

        // On spécifie qu'on utilise un encodeur en json
        $encoders = [new JsonEncoder()];

        // On instancie le "normaliseur" pour convertir la collection en tableau
        $normalizers = [new ObjectNormalizer()];

        // On fait la conversion en json
        // On instancie le convertisseur
        $serializer = new Serializer($normalizers, $encoders);

        // On convertit en json
        $jsonContent = $serializer->serialize($articles, 'json', [
            'circular_reference_handler' => function($object){
                return $object->getId();
            }
        ]);

        // On instancie la réponse
        $response = new Response($jsonContent);

        // On ajoute l'entête HTTP
        $response->headers->set('Content-Type', 'application/json');

        // On envoie la réponse
        return $response;
    }

    /**
     * @Route("/article/lire/{id}", name="lire", methods={"GET"})
     */
    public function getArticle(Articles $article)
    {
        // On spécifie qu'on utilise un encodeur en json
        $encoders = [new JsonEncoder()];

        // On instancie le "normaliseur" pour convertir la collection en tableau
        $normalizers = [new ObjectNormalizer()];

        // On fait la conversion en json
        // On instancie le convertisseur
        $serializer = new Serializer($normalizers, $encoders);

        // On convertit en json
        $jsonContent = $serializer->serialize($article, 'json', [
            'circular_reference_handler' => function($object){
                return $object->getId();
            }
        ]);

        // On instancie la réponse
        $response = new Response($jsonContent);

        // On ajoute l'entête HTTP
        $response->headers->set('Content-Type', 'application/json');

        // On envoie la réponse
        return $response;
    }

    /**
     * Ajout d'un article
     * 
     * @Route("/article/ajout", name="ajout", methods={"POST"})
     */
    public function addArticle(Request $request){
        // On vérifie si on a une requête XMLHttpRequest
        //if($request->isXmlHttpRequest()){
            // On vérifie les données après les avoir décodées
            $donnees = json_decode($request->getContent());

            // On instancie un nouvel article
            $article = new Articles();

            // On hydrate notre article
            $article->setTitre($donnees->titre);
            $article->setContenu($donnees->contenu);
            $article->setFeaturedImage($donnees->image);
            $user = $this->getDoctrine()->getRepository(Users::class)->find(2);
            $article->setUsers($user);

            // On sauvegarde en base de données
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            // On retourne la confirmation
            return new Response('Ok', 201);
        //}
        //return new Response('Erreur', 404);
    }

    /**
     * Modifie un article
     *
     * @Route("/article/editer/{id}", name="editer", methods={"PUT"})
     */
    public function editArticle(?Articles $article, Request $request){
        // On vérifie si on a une requête XMLHttpRequest
        //if($request->isXmlHttpRequest()){
            // On vérifie les données après les avoir décodées
            $donnees = json_decode($request->getContent());

            $code = 200;

            // Si on n'a pas d'article
            if(!$article){
                // On instancie un nouvel article
                $article = new Articles();

                // On met le code 201
                $code = 201;
            }
            // On hydrate notre article
            $article->setTitre($donnees->titre);
            $article->setContenu($donnees->contenu);
            $article->setFeaturedImage($donnees->image);
            $user = $this->getDoctrine()->getRepository(Users::class)->find(2);
            $article->setUsers($user);

            // On sauvegarde en base de données
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            // On retourne la confirmation
            return new Response('Ok', $code);

        //}
        //return new Response('Erreur', 404);
    }

    /**
     * Supprime un article
     *
     * @route("/article/supprimer/{id}", name="supprimer", methods={"DELETE"})
     */
    public function removeArticle(Articles $article){
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        return new Response('ok');
    }
}
