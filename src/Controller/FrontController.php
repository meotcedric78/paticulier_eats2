<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use App\Repository\SousCategorieRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\User;

class FrontController extends AbstractController
{

    /**
     * @Route ("/", name="home")
     */
    public function home(ProduitRepository $repository,CategorieRepository $categorieRepository,Request $request, SessionInterface $session , SousCategorieRepository $sousCategorieRepository): Response
    {
        $categories=$categorieRepository->findAll();
        $produits=$repository->findAll();
        $souscategories=$sousCategorieRepository->findAll();

        return $this->render("front/home.html.twig", [
            'produits'=>$produits,
            'categories'=>$categories,
            'souscategories'=>$souscategories

        ]);
    }




    /**
     * @route("/homefilter", name="homefilter")
     */
    public function homefilter(Request $request, ProduitRepository $repository, CategorieRepository $categorieRepository, SousCategorieRepository $sousCategorieRepository)
    {
        $filtre=$request->query->all();

        $categories=$categorieRepository->findAll();
        $souscategories=$sousCategorieRepository->findAll();
$cat=$request->query->get('categorie');
$souscat=$request->query->get('souscategorie');
$cp=$request->query->get('cp');

        // dd($filtre);

        if ($cat && !$cp && !$souscat):
            $produits=$repository->findBy(["categorie"=>$cat]);

        elseif($souscat && !$cp && !$cat ):
            $produits=$repository->findBy(["sousCategories"=>$souscat]);

        elseif($cp && !$cat && !$souscat ):
            $produits=$repository->findBy(["cp"=>$cp]);

        elseif($cat && $souscat && !$cp):
            $produits=$repository->findByCategorieSousCategorie($cat, $souscat);

        elseif($cat && $cp && !$souscat):
            $produits=$repository->findByCpCategorie($cat,$cp);

        elseif($souscat && $cat && $cp ):
            $produits=$repository->findByCategorieSousCategorieCp();

        elseif($cp && $souscat && !$cp ):
            $produits=$repository->findByCpSousCategorie();

        else:$produits=$repository->findAll();
        endif;
        return $this->render('front/home.html.twig',[
            "categories"=>$categories,
            "produits"=> $produits,
            "souscategories"=>$souscategories,

        ]);
    }

    /**
     * @Route("/cuisine", name="cuisine")
     */
    public function index(ProduitRepository $repository)
    {
        $produits=$repository->findAll();
        return $this->render('nos_part_cuisiniers/index.html.twig', [
            "Produits" => $produits,
        ]);
    }

    /**
     * @Route("/backofficeCuisinier", name="backofficeCuisinier")
     */
    public function backofficeCuisinier()
    {
        return $this->render('backofficeCuisinier.html.twig');
    }

    /**
     * @Route("/profil", name="profil")
     */
    public function profil()
    {
        return $this->render('front/profil.html.twig');

    }




    /**
     * @Route("/show/{id}", name="show_produit")
     */
    public function show(Produit $produit)
    {
        return $this->render('home/show.html.twig', [
            'produit'=>$produit
        ]);
    }



    /**
     * @Route("/plat", name="plat")
     */
    public function plat(produitRepository $repository)
    {
        $produits=$repository->findBy(array ('cuisinier'=>$this->getUser()));

       return $this->render('cedric/plat.html.twig',[
        'Produits'=>$produits,

       ]);
    }


    /**
     * @Route("/mission", name="mission")
     */
    public function mission()
    {
        return $this->render('mission/index.html.twig', [
            'controller_name' => 'MissionController',
        ]);
    }





}
