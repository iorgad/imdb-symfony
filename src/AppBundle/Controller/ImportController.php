<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DomCrawler\Crawler;
use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieList;
/**
 * Class ImportController
 * @package AppBundle\Controller
 */
class ImportController extends Controller
{

    const IMDB_URL = "http://www.imdb.com/sections/dvd/";

    public $list;

    public $html;

    public $listTitle;

    /**
     * @Route("/import-movies", name="import-movies")
     */
    public function importMovies(Request $request)
    {
        do {
            $items = $this->getMoviesFromFile();
        } while ($items->count() == 0 || $items->count() < 0);

        if($items->count() > 0 ) {
            do {
                $listTitle = $this->getListTitleToImport();
            } while ($listTitle == null);

            $listRepository = $this->getDoctrine()
                ->getRepository('AppBundle:MovieList');

            $movieList = $listRepository->findOneBy(array(), array('id' => 'DESC'));

            if($movieList->getTitle() == $listTitle) {
                return $this->render('@App/import/index.html.twig', array('notImported' => $listTitle
                ));
            }

            $em = $this->getDoctrine()->getManager();

            $date = new \DateTime('now');

            $this->list = new MovieList();
            $this->list->setTitle($listTitle);
            $this->list->setCreatedAt($date);
            $this->list->setUpdatedAt($date);

            $em->persist($this->list);

            $em->flush();

            $items->each(function ($node) {
                $this->importNode($node, $this->list);
            });
        }

        return $this->render('@App/import/index.html.twig', array('count' => $items->count(), 'listTitle' => $listTitle
        ));
    }

    public function importNode($node, $list) {
        $title = $node->filter('.info b a')->text();
        $url = $node->filter('.info b a')->attr('href');
        $image = $node->filter('.image a div img')->attr('src');
        $year = $node->filter('.info b .year_type')->text();
        $description = $node->filter('.info .item_description')->text();
        $rating = $node->filter('.info .rating-list .rating-rating .value')->text();

        $em = $this->getDoctrine()->getManager();

        $movie = new Movie();

        $movie->setTitle($title);
        $movie->setUrl($url);
        $movie->setImage($image);
        $movie->setListId($list->getId());
        $movie->setYear($year);
        $movie->setDescription($description);
        $movie->setRating($rating);

        $em->persist($movie);
        $em->flush();
    }

    public function getMoviesFromFile() {
        $this->getFileContentsFromURL();
        $crawler = new Crawler($this->html);

        $items = $crawler->filter('body #main .article .list_titles .detail > div');
        return $items;
    }

    public function getListTitleToImport() {
        $this->getFileContentsFromURL();
        $crawler = new Crawler($this->html);

        $title = $crawler->filter('body #main .article .list_titles h1.header');

        $title->each(function($node) {
            $this->listTitle = $node->text();
        });

        return $this->listTitle;
    }

    public function getFileContentsFromURL() {
        $this->html = file_get_contents(self::IMDB_URL);
    }
}
