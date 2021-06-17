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
    // ici on appelle la classe de annotation, qui s'appelle Route
    // chaque render de vue appelle une route à configurer. la route doit être déclarer
    // @Route("/connexion", name"connexion") "" obligatoire
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
        // fonction appelant le service panier afin de le transformer en commande,
    // ainsi chaques articles avec leur quantité enregistrés dans le panier correspondra à un achat.
    // le cumul de tout ces achats aura un seule et même id de commande et créera donc une commande reliée par l'id aux achats, eux mêmes reliés aux articles en bdd
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
        //pour sélectionner des données déjà insérées en table, on appelle le repository de notre entité concernée (table en bdd) .
        //un repository c'est une classe qui contient des méthodes permettant d'effectuer des requete de SELECT
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
        // nous avons créé une classe qui permet de générer le formulaire d'ajout de produits,
        // il faut dans le controller importer cette Type et relier le formulaire à notre instanciation d'entité produits
        $form=$this->createForm(ProduitType::class, $produit, array('ajouter'=>true));
        // on va chercher dans l'objet handlerequest qui permet de recuperer chaques données saisie des champs de formulaire.
        // il s'assure de la coordination entre formType et entity afin de générer les bon setteurs pour chaques propriété de l'entité
        $form->handleRequest($request);
        dump($produit);
        dump($request);

        // ici on informe par la condition if. que si le bouton submit a été préssé et que les données du formulaires sont conforme à notre entité (type) et à nos contrainte,
        // il peut faire intervenir doctrine et son manager pour preparer puis executer les requêtes
        if ($form->isSubmitted() && $form->isValid()):
            // on récupère ici toutes les données de l'input name="image"
            $imageFile=$form->get('image')->getData();
        $sc=$request->request->get('produit')['sousCategories'];
            // ici on place le if pour vérifier qu'une image a été uploadé dans notre input de formulaire, si oui, renverra true
            if($imageFile):
                // on redefini le nom de notre image pour s'assuré que celui ci soit unique et n'aille pas écraser un autre fichier du même nom
            $nomImage=date("YmdHis")."-".uniqid()."-".$imageFile->getClientOriginalName();

                // envoie de l'image dans images/imagesUpload
        try {
            $imageFile->move(
                $this->getParameter('images_directory'),
                $nomImage
            );
            // méthode move () attend 2 paramètres et permet de déplacer un fichier des fichier temps du server
            // vers un emplacement défini.
            //   parametre1: l'emplacement défini, paramétré au préalable dans config/service.yaml
            //    => images_directory: '%kernel.project_dir%/public/images/imagesUpload' à placer sous parameters.
            // parametre2: le nom du fichier à deplacer
        }
        catch (FileExeception $e){
            $this->redirectToRoute('add_produit.html.twig',[
                'erreur'=>$e
            ]);
        }
                // envoie du nouveau nom en BDD
        $produit->setImage($nomImage);
        endif;
        $produit->setCuisinier($this->getUser());
            $produit->setCommission(5);
       foreach ($sc as $c):
        $produit->addSousCategory($c);
        endforeach;




        $manager->persist($produit);
        $manager->flush();

            // ici si tout s'est bien passé, on donne redirection sur le site
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
