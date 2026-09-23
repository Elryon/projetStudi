<?php

namespace App\Controller;

use App\Entity\Horaires;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FooterController extends AbstractController
{
    public function footerWidget(EntityManagerInterface $entityManager): Response
    {
        $horaires = $entityManager->getRepository(Horaires::class)->findOneBy([]);

        return $this->render('partials/_footer_horaires.html.twig', [
            'horaires' => $horaires,
        ]);
    }
}