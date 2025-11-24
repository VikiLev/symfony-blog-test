<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\RegistrationFormType;
use App\Security\EmailConfirmationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        EmailConfirmationService $emailService
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $user->getPassword())
            );

            $em->persist($user);
            $em->flush();

            $emailService->sendConfirmationEmail($user);

            $this->addFlash('success', 'Registration successful! Please check your email.');

            return $this->redirectToRoute('app_register');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyEmail(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        string $secret
    ): Response {
        $id = $request->query->get('id');
        $expires = $request->query->get('expires');
        $hash = $request->query->get('hash');

        $user = $userRepository->find($id);

        if (!$user || $expires < time() || $hash !== hash_hmac('sha256', "$id|$expires", $secret)) {
            $this->addFlash('error', 'Invalid or expired link.');
            return $this->redirectToRoute('app_register');
        }

        if (!$user->isVerified()) {
            $user->setVerifiedAt(new \DateTimeImmutable());
            $em->flush();
        }

        $this->addFlash('success', 'Email verified successfully!');
        return $this->redirectToRoute('app_register');
    }

}
