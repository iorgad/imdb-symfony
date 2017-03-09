<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieList;

/**
 * Class HomeController
 * @package AppBundle\Controller
 */
class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $listRepository = $this->getDoctrine()
            ->getRepository('AppBundle:MovieList');

        $movieList = $listRepository->findOneBy(array(), array('id' => 'DESC'));

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
