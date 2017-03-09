<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DomCrawler\Crawler;
use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieList;

/**
 * Class ListController
 * @package AppBundle\Controller
 */
class ListController extends Controller
{

    /**
     * @Route("/list/{id}", name="list-details-page")
     */
    public function showDetails($id)
    {
        $listRepository = $this->getDoctrine()
            ->getRepository('AppBundle:MovieList');

        $movieList = $listRepository->findOneById($id);

        $movieRepository = $this->getDoctrine()
            ->getRepository('AppBundle:Movie');

        $movies = $movieRepository->findBy(array('list_id' => $movieList->getId()));

        $previousMovieListId = $listRepository->findPreviousMovieList($movieList->getId()) != null ? $listRepository->findPreviousMovieList($movieList->getId())->getId() : null;
        $nextMovieListId = $listRepository->findNextMovieList($movieList->getId()) != null ? $listRepository->findNextMovieList($movieList->getId())->getId() : null;


        return $this->render(
            '@App/homepage/index.html.twig',
            array(
                'movies' => $movies,
                'previousMovieListId' => $previousMovieListId,
                'nextMovieListId' => $nextMovieListId,
                'movieList' => $movieList
            )
        );
    }
}
