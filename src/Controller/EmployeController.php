<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\Plats;
use App\Entity\Regime;
use App\Entity\Theme;
use App\Form\MenuType;
use App\Form\PlatsType;
use App\Repository\MenuRepository;
use App\Repository\PlatsRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_EMPLOYE', null, "Hopopop! Pas par là, tu n'es pas un employé")]
#[Route('/employe')]
final class EmployeController extends AbstractController
{
    #[Route('', name: 'app_employe')]
    public function index(Request $request, PlatsRepository $platsRepository, MenuRepository $menuRepository, EntityManagerInterface $entityManager): Response
    {
        // Formulaire regime
        $regime = new Regime();
        $regimeForm = $this->createFormBuilder($regime)
            ->add('nom', TextType::class, ['label' => 'Nouveau régime'])
            ->getForm();

        $regimeForm->handleRequest($request);
        if ($regimeForm->isSubmitted() && $regimeForm->isValid()) {
            $entityManager->persist($regime);
            $entityManager->flush();
            $this->addFlash('success', 'Régime ajouté !');
            return $this->redirectToRoute('app_employe');
        }

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

        return $this->render('employe/index.html.twig', [
            'plats' => $platsRepository->findAll(),
            'menus' => $menuRepository->findAll(),
            'regimeForm' => $regimeForm,
            'themeForm' => $themeForm,
            'regimes' => $entityManager->getRepository(Regime::class)->findAll(),
            'themes' => $entityManager->getRepository(Theme::class)->findAll(),
        ]);
    }

    #[Route('/employe/plat', name: 'app_nouveau_plat')]
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

    #[Route('/employe/plat/{id}', name: 'app_update_plat', requirements: ['id' => '\d+'])]
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

    #[Route('/employe/menu', name: 'app_nouveau_menu')]
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

    #[Route('/employe/menu/{id}', name: 'app_update_menu', requirements: ['id' => '\d+'])]
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
}
