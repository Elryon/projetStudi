<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\StatutCommande;
use App\Enum\Statut;
use App\Form\CommandeType;
use App\Form\RegistrationFormType;
use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use App\Service\LivraisonService;
use App\Service\StatService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[IsGranted('ROLE_USER', null, "Hopopop! Pas par là, tu n'es pas connecté")]
final class UserController extends AbstractController
{
   #[Route('/utilisateur', name: 'app_user')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $user = $this->getUser();
        $commandes = $commandeRepository->findBy(
            ['user' => $user],
            ['date_commande' => 'DESC']
        );

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'commandes' => $commandes,
        ]);
    }

    #[Route('/modifier', name: 'app_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(RegistrationFormType::class, $user, [
            'is_profile' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour.');

            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/update_profile.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/commande', name: 'app_nouvelle_commande')]
    public function nouvelleCommande(Request $request, LivraisonService $livraisonService, EntityManagerInterface $entityManager, StatService $statService, MenuRepository $menuRepository, MailerInterface $mailer): Response 
    {
        $menus = $menuRepository->getMenuWithDisponiblePlats();
        $commande = new Commande();

        if ($request->query->get('menu')) {
            $menu = $entityManager->getRepository(Menu::class)->find($request->query->get('menu'));
            if ($menu) {
                $commande->setMenu($menu);
            }
        }

        $form = $this->createForm(CommandeType::class, $commande, ['menus' => $menus,]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $menu = $commande->getMenu();

            if ($commande->getNombrePersonne() < $menu->getPersonneMin()) {
                $this->addFlash('error', sprintf(
                    'Le menu "%s" requiert un minimum de %d personnes.',
                    $menu->getTitre(),
                    $menu->getPersonneMin()
                ));

                return $this->render('user/commande.html.twig', [
                    'commandeForm' => $form,
                    'menus' => $menus,
                ]);
            }

            $commande->setUser($this->getUser());
            $commande->setDateCommande(new \DateTime());
            $commande->setMaterielRendu(false);

            $statutCommande = new StatutCommande;
            $statutCommande->setStatut(Statut::Attente);
            $statutCommande->setDate(new \DateTime());

            $commande->addStatuts($statutCommande);
            $entityManager->persist($statutCommande);

            $prixRepas = $menu->getPrixPersonne() * $commande->getNombrePersonne();

            $livraison = $livraisonService->calculerFraisLivraison($commande->getAdresse());
                $commande->setPrixLivraison($livraison['frais']);
            if($commande->getNombrePersonne() > $menu->getPersonneMin() + 4){
                $commande->setPrixTotal(($prixRepas*0.9) + $livraison['frais']);
            }else{
                $commande->setPrixTotal($prixRepas + $livraison['frais']);
            }

            $entityManager->persist($commande);
            $menu->setQuantiteRestante(($menu->getQuantiteRestante()-$commande->getNombrePersonne()));
            $entityManager->flush();
            $statService->enregistrerCommande($commande);

            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@elryon.fr', 'no reply Vite & Gourmand'))
                ->to((string) $commande->getUser()->getEmail())
                ->subject('Confirmation de votre commande - Vite & Gourmand')
                ->htmlTemplate('emails/commande_cree.html.twig')
                ->context([
                    'commande' => $commande,
                    'menu' => $menu,
                    'prixRepas' => $prixRepas,
                    'livraison' => $livraison,
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Commande passée avec succès !');

            return $this->redirectToRoute('app_detail_commande', ['id' => $commande->getId()]);
        }

        return $this->render('user/commande.html.twig', [
            'commandeForm' => $form,
            'menus' => $menus,
        ]);
    }

    #[Route('/commande/estimer-prix', name: 'app_commande_estimer_prix', methods: ['POST'])]
    public function estimerPrix(Request $request, MenuRepository $menuRepository, LivraisonService $livraisonService): JsonResponse {
        $menuId = $request->request->get('menu');
        $nombrePersonne = (int) $request->request->get('nombre_personne');
        $adresse = $request->request->get('adresse');

        $menu = $menuRepository->find($menuId);

        if (!$menu) {
            return $this->json(['error' => 'Menu introuvable.'], 400);
        }

        if ($nombrePersonne < $menu->getPersonneMin()) {
            return $this->json(['error' => sprintf(
                'Le menu "%s" requiert un minimum de %d personnes.',
                $menu->getTitre(),
                $menu->getPersonneMin()
            )], 400);
        }

        if (!$adresse) {
            return $this->json(['error' => 'Adresse manquante.'], 400);
        }

        try {
            $livraison = $livraisonService->calculerFraisLivraison($adresse);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $prixRepas = $menu->getPrixPersonne() * $nombrePersonne;
        $prixTotal = $prixRepas + $livraison['frais'];

        return $this->json([
            'menu' => $menu->getTitre(),
            'nombre_personne' => $nombrePersonne,
            'prix_repas' => round($prixRepas, 2),
            'frais_livraison' => $livraison['frais'],
            'gratuit' => $livraison['gratuit'],
            'ville' => $livraison['ville'],
            'prix_total' => round($prixTotal, 2),
        ]);
    }

    #[Route('/commande/{id}', name: 'app_detail_commande')]
    public function detailCommande(Commande $commande, Request $request, EntityManagerInterface $entityManager): Response 
    {
        if ($commande->getUser() !== $this->getUser() && !$this->isGranted('ROLE_EMPLOYE')) {
            return $this->redirectToRoute('app_home');
        }

        $avisForm = null;

        if (
            $commande->getDernierStatut()->getStatut() === Statut::Termine && !$commande->getAvis() && $commande->getUser() === $this->getUser()
        ) {
            $avis = new Avis();
            $avisForm = $this->createFormBuilder($avis)
                ->add('note', IntegerType::class, [
                    'label' => 'Note (1 à 5)',
                    'attr' => ['min' => 1, 'max' => 5],
                ])
                ->add('contenu', TextareaType::class, [
                    'label' => 'Votre avis',
                ])
                ->getForm();

            $avisForm->handleRequest($request);
            if ($avisForm->isSubmitted() && $avisForm->isValid()) {
                $avis->setCommande($commande);
                $avis->setValidated(false);
                $entityManager->persist($avis);
                $entityManager->flush();
                $this->addFlash('success', 'Avis envoyé, il sera visible après validation.');
                return $this->redirectToRoute('app_detail_commande', ['id' => $commande->getId()]);
            }
        }

        return $this->render('user/detail_commande.html.twig', [
            'commande' => $commande,
            'avisForm' => $avisForm,
        ]);
    }

    #[Route('/commande/{id}/modifier', name: 'app_modifier_commande')]
    public function modifierCommande(Commande $commande, Request $request, LivraisonService $livraisonService, EntityManagerInterface $entityManager, StatService $statService, MailerInterface $mailer): Response 
    {
        if ($commande->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($commande->getDernierStatut()->getStatut() !== Statut::Attente) {
            $this->addFlash('error', 'Cette commande ne peut plus être modifiée.');
            return $this->redirectToRoute('app_detail_commande', ['id' => $commande->getId()]);
        }

        $ancienneQuantitePersonne = $commande->getNombrePersonne();

        $form = $this->createForm(CommandeType::class, $commande, [
            'modifier' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $menu = $commande->getMenu();

            if ($commande->getNombrePersonne() < $menu->getPersonneMin()) {
                $this->addFlash('error', sprintf(
                    'Le menu "%s" requiert un minimum de %d personnes.',
                    $menu->getTitre(),
                    $menu->getPersonneMin()
                ));

                return $this->render('user/update_commande.html.twig', [
                    'commandeForm' => $form,
                    'commande' => $commande,
                ]);
            }

            $quantiteDisponible = $menu->getQuantiteRestante() + $ancienneQuantitePersonne;
            $nouvelleQuantiteRestante = $quantiteDisponible - $commande->getNombrePersonne();

            if ($nouvelleQuantiteRestante < 0) {
                $this->addFlash('error', sprintf(
                    'Il ne reste que %d couvert(s) disponible(s) pour le menu "%s".',
                    $quantiteDisponible,
                    $menu->getTitre()
                ));

                return $this->render('user/update_commande.html.twig', [
                    'commandeForm' => $form,
                    'commande' => $commande,
                ]);
            }

            $menu->setQuantiteRestante($nouvelleQuantiteRestante);

            $prixRepas = $menu->getPrixPersonne() * $commande->getNombrePersonne();
            $livraison = $livraisonService->calculerFraisLivraison($commande->getAdresse());
            $commande->setPrixLivraison($livraison['frais']);
            $commande->setPrixLivraison($livraison['frais']);

            if ($commande->getNombrePersonne() > $menu->getPersonneMin() + 4) {
                $commande->setPrixTotal(($prixRepas * 0.9) + $livraison['frais']);
            } else {
                $commande->setPrixTotal($prixRepas + $livraison['frais']);
            }

            $entityManager->flush();
            $statService->enregistrerCommande($commande);

            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@elryon.fr', 'no reply Vite & Gourmand'))
                ->to((string) $commande->getUser()->getEmail())
                ->subject('Votre commande a été modifiée - Vite & Gourmand')
                ->htmlTemplate('emails/commande_modifie.html.twig')
                ->context([
                    'commande' => $commande,
                    'menu' => $menu,
                    'prixRepas' => $prixRepas,
                    'livraison' => $livraison,
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Commande modifiée avec succès !');

            return $this->redirectToRoute('app_detail_commande', ['id' => $commande->getId()]);
        }

        return $this->render('user/update_commande.html.twig', [
            'commandeForm' => $form,
            'commande' => $commande,
        ]);
    }

    #[Route('/commande/{id}/annuler', name: 'app_annuler_commande', methods: ['POST'])]
    public function annulerCommande(Commande $commande, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($commande->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($commande->getDernierStatut()->getStatut() !== Statut::Attente) {
            $this->addFlash('error', 'Cette commande ne peut plus être annulée.');
            return $this->redirectToRoute('app_detail_commande', ['id' => $commande->getId()]);
        }

        if ($this->isCsrfTokenValid('annuler' . $commande->getId(), $request->request->get('_token'))) {
            $menu = $commande->getMenu();
            $menu->setQuantiteRestante($menu->getQuantiteRestante() + $commande->getNombrePersonne());

            $entityManager->remove($commande);
            $entityManager->flush();
            $this->addFlash('success', 'Commande annulée.');
            return $this->redirectToRoute('app_user');
        }

        return $this->redirectToRoute('app_detail_commande', ['id' => $commande->getId()]);
    }
}
