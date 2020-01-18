<?php

namespace App\Controller;

use App\Entity\Articles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    /**
     * @Route("/sitemap.xml", name="sitemap", defaults={"_format"="xml"})
     */
    public function index(Request $request)
    {
        // On récupère le don d'hôte depuis l'URL
        $hostname = $request->getSchemeAndHttpHost();

        // On initialise un tableau pour lister les URLs
        $urls = []; // ou array()

        // On ajoute les URLs "statiques"
        $urls[] = ['loc' => $this->generateUrl('accueil')];
        $urls[] = ['loc' => $this->generateUrl('app_login')];
        $urls[] = ['loc' => $this->generateUrl('app_register')];

        // on ajoute les URLs "dynamiques"
        foreach($this->getDoctrine()->getRepository(Articles::class)->findAll() as $article){
            $images = [
                'loc' => '/uploads/images/featured/'. $article->getFeaturedImage(),
                'title' => $article->getTitre()
            ];

            $urls[] = [
                'loc' => $this->generateUrl('article', [
                    'slug' => $article->getSlug()
                ]),
                'image' => $images,
                'lastmod' => $article->getUpdatedAt()->format('Y-m-d')
            ];
        }
    
        // Fabrication de la réponse
        $response = new Response(
            $this->renderView('sitemap/index.html.twig', [
                'urls' => $urls,
                'hostname' => $hostname
            ]), // compact('urls', 'hostname')
            200
        );

        // Ajout des entêtes HTTP
        $response->headers->set('Content-Type', 'text/xml');

        // On envoie la réponse
        return $response;
    }
}
