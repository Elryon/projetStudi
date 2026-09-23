<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Commande;
use App\Entity\Horaires;
use App\Entity\Menu;
use App\Entity\Plats;
use App\Entity\Regime;
use App\Entity\StatutCommande;
use App\Entity\Theme;
use App\Enum\Statut;
use App\Form\HorairesType;
use App\Form\MenuType;
use App\Form\PlatsType;
use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use App\Repository\PlatsRepository;
use App\Service\StatService;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_EMPLOYE', null, "Hopopop! Pas par là, tu n'es pas un employé")]
#[Route('/employe')]
final class EmployeController extends AbstractController
{
    #[Route('', name: 'app_employe')]
    public function index(Request $request, PlatsRepository $platsRepository, MenuRepository $menuRepository, EntityManagerInterface $entityManager, CommandeRepository $commandeRepository): Response
    {
        // Formulaire theme
        $theme = new Theme();
        $themeForm = $this->createFormBuilder($theme)
            ->add('theme', TextType::class, ['label' => 'Nouveau thème'])
            ->getForm();

        $themeForm->handleRequest($request);
        if ($themeForm->isSubmitted() && $themeForm->isValid()) {
            $entityManager->persist($theme);
            $entityManager->flush();
            $this->addFlash('success', 'Thème ajouté !');
            return $this->redirectToRoute('app_employe');
        }

        // Formulaire horaires
        $horaires = $entityManager->getRepository(Horaires::class)->findOneBy([]);
        if (!$horaires) {
            $horaires = new Horaires();
            $horaires->setLundi('Fermé')
                ->setMardi('Fermé')
                ->setMercredi('Fermé')
                ->setJeudi('Fermé')
                ->setVendredi('Fermé')
                ->setSamedi('Fermé')
                ->setDimanche('Fermé');
        }

        $horairesForm = $this->createForm(HorairesType::class, $horaires);
        $horairesForm->handleRequest($request);
        if ($horairesForm->isSubmitted() && $horairesForm->isValid()) {
            $entityManager->persist($horaires);
            $entityManager->flush();
            $this->addFlash('success', 'Horaires mis à jour !');
            return $this->redirectToRoute('app_employe');
        }

        $filtreStatut = $request->query->get('statut');
        $filtreClient = $request->query->get('client');

        $query = $commandeRepository->createQueryBuilder('commande')
            ->leftJoin('commande.user', 'u')
            ->orderBy('commande.date_commande', 'DESC');

        if ($filtreClient) {
            $query->andWhere('LOWER(u.nom) LIKE :client OR LOWER(u.prenom) LIKE :client OR LOWER(u.email) LIKE :client')
            ->setParameter('client', '%' . strtolower($filtreClient) . '%');
        }

        $commandes = $query->getQuery()->getResult();

        return $this->render('employe/index.html.twig', [
            'plats' => $platsRepository->findAll(),
            'menus' => $menuRepository->findAll(),
            'themeForm' => $themeForm,
            'themes' => $entityManager->getRepository(Theme::class)->findAll(),
            'commandes' => $commandes,
            'filtreStatut' => $filtreStatut,
            'filtreClient' => $filtreClient,
            'horairesForm' => $horairesForm,
            'avisEnAttente' => $entityManager->getRepository(Avis::class)->findBy(['validated' => false]),
        ]);
    }

    #[Route('/plat', name: 'app_nouveau_plat')]
    public function nouveauPlat(Request $request, EntityManagerInterface $entityManager, UploadService $uploadService): Response
    {
        $plat = new Plats();
        $form = $this->createForm(PlatsType::class, $plat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();

            if($photoFile){
                $newFilename = $uploadService -> upload($photoFile);
                $plat->setPhoto($newFilename);
            }

            $plat->setDisponible(true);

            $entityManager->persist($plat);
            $entityManager->flush();

            $this->addFlash('success', 'Le plat a été créé avec succès !');

            return $this->redirectToRoute('app_employe');
        }

        return $this->render('employe/create_plat.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/plat/{id}', name: 'app_update_plat')]
    public function updatePlat(Plats $plat, Request $request, EntityManagerInterface $entityManager, UploadService $uploadService): Response
    {
        $form = $this->createForm(PlatsType::class, $plat, [
            'exist' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();

            if($photoFile){
                $newFilename = $uploadService -> upload($photoFile, $plat->getPhoto());
                $plat->setPhoto($newFilename);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le plat a été modifié avec succès !');

            return $this->redirectToRoute('app_employe');
        }

        return $this->render('employe/update_plat.html.twig', [
            'plats' => $plat,
            'registrationForm' => $form,
        ]);
    }

    #[Route('/menu', name: 'app_nouveau_menu')]
    public function nouveauMenu(Request $request, EntityManagerInterface $entityManager): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($menu);
            $entityManager->flush();

            $this->addFlash('success', 'Le menu a été créé avec succès !');

            return $this->redirectToRoute('app_employe');
        }

        return $this->render('employe/create_menu.html.twig', [
            'menuForm' => $form,
        ]);
    }

    #[Route('/menu/{id}', name: 'app_update_menu')]
    public function updateMenu(Menu $menu, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le menu a été modifié avec succès !');

            return $this->redirectToRoute('app_employe');
        }

        return $this->render('employe/update_menu.html.twig', [
            'menu' => $menu,
            'menuForm' => $form,
        ]);
    }

    #[Route('/commande/{id}/avancer', name: 'app_avancer_commande', methods: ['POST'])]
    public function avancerCommande(Commande $commande, EntityManagerInterface $entityManager, StatService $statService, MailerInterface $mailer): Response
    {
        $next = match ($commande->getDernierStatut()->getStatut()) {
            Statut::Attente => Statut::Accepte,
            Statut::Accepte => Statut::Preparation,
            Statut::Preparation => Statut::Livraison,
            Statut::Livraison => Statut::Livre,
            Statut::Livre => $commande->isPretMateriel() ? Statut::AttenteRetourMateriel : Statut::Termine,
            Statut::AttenteRetourMateriel => Statut::Termine,
            default => null,
        };

        if ($next) {
            $statutCommande = new StatutCommande();
            $statutCommande->setStatut($next);
            $statutCommande->setDate(new \DateTime());

            $commande->addStatuts($statutCommande);
            
            if ($next === Statut::Termine && $commande->isPretMateriel()) {
                $commande->setMaterielRendu(true);
            }

            $entityManager->persist($statutCommande);
            $entityManager->flush();
            $statService->enregistrerCommande($commande);

            if ($next === Statut::AttenteRetourMateriel) {
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@elryon.fr', 'no reply Vite & Gourmand'))
                    ->to((string) $commande->getUser()->getEmail())
                    ->subject('Rappel : retour du matériel loué - Vite & Gourmand')
                    ->htmlTemplate('emails/attente_retour_materiel.html.twig')
                    ->context([
                        'commande' => $commande,
                    ]);

                $mailer->send($email);
            }

            if ($next === Statut::Termine) {
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@elryon.fr', 'no reply Vite & Gourmand'))
                    ->to((string) $commande->getUser()->getEmail())
                    ->subject('Votre commande est terminée - Vite & Gourmand')
                    ->htmlTemplate('emails/commande_termine.html.twig')
                    ->context([
                        'commande' => $commande,
                    ]);

                $mailer->send($email);
            }

            $this->addFlash('success', 'Commande passée en "' . $next->getLabel() . '".');
        }

        return $this->redirectToRoute('app_employe');
    }

    #[Route('/commande/{id}/annuler', name: 'app_employe_annuler_commande', methods: ['POST'])]
    public function annulerCommandeEmploye(Commande $commande, Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $modeContact = $request->request->get('mode_contact');
        $motif = $request->request->get('motif');

        if (!$modeContact || !$motif) {
            $this->addFlash('error', 'Veuillez renseigner le mode de contact et le motif d\'annulation.');
            return $this->redirectToRoute('app_employe');
        }

        $statutCommande = new StatutCommande;
        $statutCommande->setStatut(Statut::Annule);
        $statutCommande->setDate(new \DateTime());

        $commande->addStatuts($statutCommande);
        $entityManager->persist($statutCommande);
        $commande->setMotifAnnulation($motif);
        $commande->setModeContact($modeContact);

        $menu = $commande->getMenu();
        if ($menu !== null) {
            $menu->setQuantiteRestante($menu->getQuantiteRestante() + $commande->getNombrePersonne());
        }

        $entityManager->flush();

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@elryon.fr', 'no reply Vite & Gourmand'))
            ->to((string) $commande->getUser()->getEmail())
            ->subject('Votre commande a été annulée - Vite & Gourmand')
            ->htmlTemplate('emails/commande_annule.html.twig')
            ->context([
                'commande' => $commande,
            ]);
        $mailer->send($email);

        $this->addFlash('success', 'Commande annulée.');

        return $this->redirectToRoute('app_employe');
    }

    #[Route('/avis/{id}/valider', name: 'app_valider_avis', methods: ['POST'])]
    public function validerAvis(Avis $avis, Request $request, EntityManagerInterface $entityManager): Response
    {
        $action = $request->request->get('action');

        if ($action === 'valider') {
            $avis->setValidated(true);
            $entityManager->flush();
            $this->addFlash('success', 'Avis validé.');
        } elseif ($action === 'refuser') {
            $entityManager->remove($avis);
            $entityManager->flush();
            $this->addFlash('success', 'Avis refusé et supprimé.');
        }

        return $this->redirectToRoute('app_employe');
    }
}
