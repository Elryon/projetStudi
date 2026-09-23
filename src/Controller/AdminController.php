<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\StatService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, Request $request, StatService $statService, MailerInterface $mailer ): Response 
    {
        $admin = $this->getUser();
        $user = new User();
        $form = $this->createFormBuilder($user)
            ->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner le nom.']),
                ],
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner le prénom.']),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => "L'adresse mail est obligatoire."]),
                    new Email(['message' => "L'adresse mail est invalide."]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe.']),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Le mot de passe doit faire au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/',
                        'message' => 'Le mot de passe doit contenir une majuscule, une minuscule, un chiffre et un caractère spécial.',
                    ]),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_EMPLOYE']);

            $entityManager->persist($user);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@elryon.fr', 'no reply Vite & Gourmand'))
                ->to((string) $user->getEmail())
                ->subject('Bienvenue dans l\'équipe Vite & Gourmand !')
                ->htmlTemplate('emails/compte_employe_cree.html.twig')
                ->context([
                    'user' => $user,
                    'admin' => $admin,
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Le compte employé a été créé avec succès !');

            return $this->redirectToRoute('app_admin');
        }

        $employes = $entityManager->getRepository(User::class)->findByRole('ROLE_EMPLOYE');
        $data = $statService->getStats();

        return $this->render('admin/index.html.twig', [
            'employeForm' => $form,
            'employes' => $employes ?? [],
            'parMenu' => $data['commandesParMenu'],
            'caParMenu' => $data['caParMenu'],
            'caTotal' => $data['caTotal'],
            'menuTitres' => $statService->getMenuTitres(),
        ]);
    }

    #[Route('/stats/api', name: 'app_admin_stats_api')]
    public function statsApi(StatService $statService, Request $request): Response
    {
        return $this->json($statService->getStats(
            $request->query->get('menu'),
            $request->query->get('debut'),
            $request->query->get('fin')
        ));
    }

    #[Route('/retrograder/{id}', name: 'app_retrograder', methods: ['POST'])]
    public function retrograderEmploye(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setRoles(['ROLE_USER']);
        $entityManager->flush();

        $this->addFlash('success', sprintf('%s %s n\'est plus employé.', $user->getPrenom(), $user->getNom()));

        return $this->redirectToRoute('app_admin');
    }
}