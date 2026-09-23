<?php

namespace App\Controller;

use App\Dto\ContactRequest;
use App\Entity\Menu;
use App\Entity\Theme;
use App\Entity\User;
use App\Enum\Regime;
use App\Form\ContactType;
use App\Repository\AvisRepository;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(AvisRepository $avisRepository): Response
    {
        $avis = $avisRepository->getValidatedAvis();
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'avis' => $avis,
        ]);
    }

    #[Route('/menus', name: 'app_menus')]
    public function menus(MenuRepository $menuRepository, EntityManagerInterface $entityManager): Response
    {
        $menus = $menuRepository->getMenuWithDisponiblePlats();
        return $this->render('home/menus.html.twig', [
            'menus' => $menus,
            'themes' => $entityManager->getRepository(Theme::class)->findAll(),
            'regimes' => Regime::cases(),
        ]);
    }

    #[Route('/menu/{id}', name: 'app_details_menu')]
    public function detailMenu(Menu $menu): Response
    {
        return $this->render('home/menus_details.html.twig', [
            'menu' => $menu,
        ]);
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response 
    {
        $contactRequest = new ContactRequest();
        $form = $this->createForm(ContactType::class, $contactRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $employes = $entityManager->getRepository(User::class)->findByRole('ROLE_EMPLOYE');

            if (empty($employes)) {
                $this->addFlash('error', 'Impossible d\'envoyer votre message pour le moment. Merci de réessayer plus tard.');
                return $this->render('contact/index.html.twig', [
                    'contactForm' => $form,
                ]);
            }

            foreach ($employes as $employe) {
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@elryon.fr', 'no reply Vite & Gourmand'))
                    ->to((string) $employe->getEmail())
                    ->replyTo($contactRequest->email)
                    ->subject('Nouveau message de contact : ' . $contactRequest->titre)            
                    ->htmlTemplate('emails/contact.html.twig')
                    ->context([
                    'contactRequest' => $contactRequest,
                ]);

                $mailer->send($email);
            }

            $this->addFlash('success', 'Votre message a bien été envoyé. Nous vous répondrons rapidement.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('home/contact.html.twig', [
            'contactForm' => $form,
        ]);
    }

    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('legal/mentions_legales.html.twig');
    }

    #[Route('/cgv', name: 'app_cgv')]
    public function cgv(): Response
    {
        return $this->render('legal/cgv.html.twig');
    }
}