<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/menus', name: 'app_menus')]
    public function menus(MenuRepository $menuRepository): Response
    {
        $menus = $menuRepository->getMenuWithDisponiblePlats();
        return $this->render('home/menus.html.twig', [
            'menus' => $menus
        ]);
    }

    #[Route('/menu/{id}', name: 'app_details_menu', requirements: ['id' => '\d+'])]
    public function detailMenu(Menu $menu): Response
    {
        return $this->render('home/menus_details.html.twig', [
            'menu' => $menu,
        ]);
    }
}