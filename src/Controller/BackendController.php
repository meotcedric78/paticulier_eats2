<?php

namespace App\Controller;

use App\Entity\Achat;
use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\SousCategorie;
use App\Entity\User;
use App\Entity\Produit;
use Swift_Image;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use App\Form\CategorieType;
use App\Form\ProduitType;
use App\Form\SousCategorieType;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\SousCategorieRepository;
use App\Repository\UserRepository;
use App\Service\Panier\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin")
 */
class BackendController extends AbstractController
{
    /**
     * @Route("/", name="backoffice")
     */
    public function backoffice()
    {
        return $this->render('backoffice.html.twig');
    }

    /**
     * @Route("/panier", name="panier")
     */
    public function panier(PanierService $panierService, ProduitRepository $produitRepository)
    {
        $produits = $produitRepository->findAll();

        return $this->render("home/panier.html.twig",[
            'items' => $panierService->getFullPanier(),
            'total' => $panierService->getTotal(),
            'produits'=>$produits
        ]);
    }

    /**
     * @Route("/ajoutpanier/{id}/{param}", name="ajout_panier")
     */
    public function ajoutPanier($id, $param ,PanierService $panierService)
    {

        $panierService->add($id);

        if ($param=="home"):
            return $this->redirectToRoute('home');
        elseif ($param=="panier"):
            return $this->redirectToRoute('panier');
        endif;

    }

    /**
     * @Route("/retraitpanier/{id}", name="retrait_panier")
     */
    public function retraitPanier($id ,PanierService $panierService)
    {
        $panierService->remove($id);

        return $this->redirectToRoute('panier');

    }

    /**
     * @Route("/annulepanier/{id}", name="annule_panier")
     */
    public function annulePanier($id ,PanierService $panierService)
    {
        $panierService->delete($id);

        return $this->redirectToRoute('panier');

    }

    /**
     * @Route("/commande", name="commande")
     */
    public function commande(PanierService $panierService, EntityManagerInterface $manager)
    {
        $panier = $panierService->getFullPanier();

        $commande = new Commande();
        $commande->setTotal($panierService->getTotal());
        $commande->setUser($this->getUser());

        foreach ($panier as $item) {
            $produit=$item['produit'];
            $achat = new Achat();
            $achat->setProduit($item['produit']);
            $achat->setQuantite($item['quantite']);
            $achat->setPrix($item['produit']->getPrix());
           // $produit->setStock($produit->getStock()-$item['quantite']);
            $manager->persist($achat);
            $manager->persist($produit);
            $achat->setCommande($commande);
            $panierService->delete($item['produit']->getId());
        }

        $commande->setDate(new \DateTime());
        $manager->persist($commande);
        $manager->flush();
        $this->addFlash('success', 'Commande validée');

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/gestioncommandes", name="gestion_commandes")
     */
    public function gestionCommandes(CommandeRepository $repository)
    {
        $commandes=$repository->findAll();
        $this->addFlash('success', 'La catégorie a bien été créée!!');
        return $this->render('cedric/gestion_commandes.html.twig',[
            'commandes'=>$commandes,

        ]);
    }

    /**
     * @Route("/add", name="add_produit")
     */
    public function add(Request $request, EntityManagerInterface $manager)
    {
        dump($request);
        $produit= new Produit();
        dump($produit);
        $form=$this->createForm(ProduitType::class, $produit, array('ajouter'=>true));
        $form->handleRequest($request);
        dump($produit);
        dump($request);


        if ($form->isSubmitted() && $form->isValid()):
            $imageFile=$form->get('image')->getData();
        $sc=$request->request->get('produit')['sousCategories'];
        if($imageFile):
            $nomImage=date("YmdHis")."-".uniqid()."-".$imageFile->getClientOriginalName();

        try {
            $imageFile->move(
                $this->getParameter('images_directory'),
                $nomImage
            );
        }
        catch (FileExeception $e){
            $this->redirectToRoute('add_produit.html.twig',[
                'erreur'=>$e
            ]);
        }
        $produit->setImage($nomImage);
        endif;
        $produit->setCuisinier($this->getUser());
            $produit->setCommission(5);
       foreach ($sc as $c):
        $produit->addSousCategory($c);
        endforeach;




        $manager->persist($produit);
        $manager->flush();

        $this->addFlash("success", "Le produit à bien été ajouté");

        return $this->redirectToRoute("gestion_produit");
        endif;
        return $this->render('cedric/add_produit.html.twig',[
            'formProduit'=>$form->createView()
        ]);

    }
    /**
     * @Route("/produit_modif/{id}" , name="produit_modif")
     */
    public function modifier_produit(Produit $produit, Request $request, EntityManagerInterface $manager )
    {
        dump($produit);


        $form = $this->createForm(ProduitType::class, $produit, array(
            'modifier' => true
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $imageFile = $form->get('imageFile')->getData();


            if($imageFile)
            {
                $nomImage = date("YmdHis") . "-" . uniqid() . "-" . $imageFile->getClientOriginalName();

                try
                {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $nomImage
                    );

                }
                catch(FileException $e)
                {

                }

                if(!empty($produit->getImage() ))
                {
                    unlink($this->getParameter('images_directory') .'/'. $produit->getImage());
                }

                $produit->setImage($nomImage);

            }

            $manager->persist($produit);
            $manager->flush();

            return $this->redirectToRoute("gestion_produit");

        }
        return $this->render("cedric/modifier_produit.html.twig" , [
            "formProduit" => $form->createView(),
            "produit" => $produit
        ]);
    }

    /**
     * @Route("/produit/{id}/delete", name="produit_delete")
     * @param Produit $produit
     * @return RedirectResponse
     */
    public function deleteProduit(Produit $produit): RedirectResponse
    {
        $em=$this->getDoctrine()->getManager();
        $em->remove($produit);
        $em->flush();
        return $this->redirectToRoute('gestion_produit');
    }

    /**
     * @Route("/gestion_produit", name="gestion_produit")
     */
    public function gestionProduits(ProduitRepository $repository)
    {
        $produits= $repository->findAll();

        return $this->render('cedric/gestion_produit.html.twig',[
            'produits'=> $produits
        ]);

    }

    /**
     * @Route("/utilisateur", name="utilisateurs")
     */
    public function utilisateurs(UserRepository $repository)
    {
        $utilisateurs=$repository->findAll();

        return $this->render('backend/utilisateurs.html.twig',[
            'utilisateurs'=>$utilisateurs
        ]);
    }

    /**
     * @Route("/deleteuser/{id}", name="delete_user")
     */
    public function delete_user(User $user, EntityManagerInterface $manager)
    {
       $manager->remove($user);
       $manager->flush();
       $this->addFlash('succes', 'L\'utilisateur a bien été supprimé');
       return $this->redirectToRoute('utilisateurs');
    }

    /**
     * @Route("/deleteuser/{id}", name="delete_user")
    */
    public function delete_user_repository(UserRepository $repository,$id, EntityManagerInterface $manager)
    {
        $user=$repository->find($id);


       $manager->remove($user);
        $manager->flush();
        $this->addFlash('succes', 'L\'utilisateur a bien été supprimé');
        return $this->redirectToRoute('utilisateurs');
    }







    /**
     * @Route("/ajout_souscategorie", name="ajout_souscategorie")
     * @Route("/modif_souscategorie/{id}", name="modif_souscategorie")
     */
    public function ajoutsousCategorie(Request $request,EntityManagerInterface $manager, SousCategorie $souscategorie=null)
    {
        if (!$souscategorie):
            $souscategorie=new SousCategorie();
        endif;

        $form=$this->createForm(SousCategorieType::class, $souscategorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            $manager->persist($souscategorie);
            $manager->flush();

            $this->addFlash('success', 'La sous-catégorie a bien été créée!!');
            return $this->redirectToRoute('gestion_souscategorie');

        endif;



        return $this->render('cedric/souscategorie/ajout_souscategorie.html.twig', [
            'form_souscategorie'=> $form->createView()
        ]);
    }

    /**
     * @Route("/delete_souscategorie/{id}", name="delete_souscategorie")
     */
    public function deletesousCategorie(SousCategorie $souscategorie, EntityManagerInterface $manager)
    {

        $manager->remove($souscategorie);
        $manager->flush();
        $this->addFlash('success', 'La sous-catégorie a bien été supprimée !!');
        return $this->redirectToRoute('gestion_souscategorie');
    }

    /**
     * @Route("/gestion_souscategorie", name="gestion_souscategorie")
     */
    public function gestionsousCategorie(SousCategorieRepository $repository)
    {
        $souscategories=$repository->findAll();
        return $this->render('cedric/souscategorie/gestion_souscategorie.html.twig', [
            'souscategories'=>$souscategories

        ]);
    }



    /**
     * @Route("/ajout_categorie", name="ajout_categorie")
     * @Route("/modif_categorie/{id}", name="modif_categorie")
     */
    public function ajoutCategorie(Request $request,EntityManagerInterface $manager, Categorie $categorie=null)
    {
        if (!$categorie):
            $categorie=new Categorie();
        endif;

        $form=$this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            $manager->persist($categorie);
            $manager->flush();

            $this->addFlash('success', 'La catégorie a bien été créée!!');
            return $this->redirectToRoute('gestion_categorie');

        endif;



        return $this->render('cedric/categorie/ajout_categorie.html.twig', [
            'form_categorie'=> $form->createView(),

        ]);


    }

    /**
     * @Route("/delete_categorie/{id}", name="delete_categorie")
     */
    public function deleteCategorie(Categorie $categorie, EntityManagerInterface $manager)
    {

        $manager->remove($categorie);
        $manager->flush();
        $this->addFlash('success', 'La catégorie a bien été supprimée !!');
        return $this->redirectToRoute('gestion_categorie');
    }

    /**
     * @Route("/gestion_categorie", name="gestion_categorie")
     */
    public function gestionCategorie(CategorieRepository $repository)
    {
        $categories=$repository->findAll();
        return $this->render('cedric/categorie/gestion_categorie.html.twig', [
            'categories'=>$categories,


        ]);
    }


    /**
     * @Route("/mail", name="mail")
     */
    public function send_email(request $request)
    {

        if (!empty($request->request)):
            // dd($request->request->get('email'));
            $transporter = (new Swift_SmtpTransport('smtp-mail.outlook.com', 587, 'tls'))
                ->setUsername('parteats@outlook.com')
                ->setPassword('symfonypart78'); // (à changer password)

            $mailer = new Swift_Mailer($transporter);
            $mess=$request->request->get('message');
            $nom=$request->request->get('surname');
            $prenom=$request->request->get('name');
            $motif=$request->request->get('need');

            $message = (new Swift_Message("$motif"))
                ->setFrom($request->request->get('email'))
                ->setTo(['parteats@outlook.com'=> 'PartEats']);
            $cid = $message->embed(Swift_Image::fromPath('images/cuisine/livraison.jpg'));

            $message->setBody(

                $this->renderView('cedric/test.html.twig',[
                    'message'=>$mess,
                    'nom'=>$nom,
                    'prenom'=>$prenom,
                    'motif'=>$motif,
                    'email'=>$request->request->get('email'),
                    'cid'=>$cid
                ]),
                'text/html'
            );


// Send the message
            $result = $mailer->send($message);


            $this->addFlash('success', 'email envoyé');
            return $this->redirectToRoute('home');
        endif;
    }

    /**
     * @Route("/sendform", name="send_form")
     */
    public function form_email()
    {
        return $this->render('cedric/Email/mail.html.twig');
    }








}
