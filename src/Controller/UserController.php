<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Admin\Comment;
use App\Form\Admin\CommentType;
use App\Repository\UserRepository;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Admin\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('user/show.html.twig');
    }

    #[Route('/comments', name: 'app_user_comments', methods: ['GET'])]
    public function comments(CommentRepository $commentRepository): Response
    {
        /** @var User $user  */
        $user = $this->getUser();
        $comments=$commentRepository->getAllCommentsUser($user->getId());
        return $this->render('user/comments.html.twig',[
            'comments'=>$comments,
        ]);
    }

    #[Route('/hotel', name: 'app_user_hotel', methods: ['GET'])]
    public function hotels(HotelRepository $hotelRepository): Response
    {
        /** @var User $user  */
        $user = $this->getUser(); // Get login User data

        return $this->render('user/hotels.html.twig', [
            'hotels' => $hotelRepository->findBy(['userid'=>$user->getId()]),
        ]);
    }

    #[Route('/reservations', name: 'app_user_reservations', methods: ['GET'])]
    public function reservations(): Response
    {
        return $this->render('user/reservations.html.twig');
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $file = $form->get('image')->getData();

            if ($file) {

                $hedefDizin = 'uploads/images';
                $dosyaAdi = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($hedefDizin, $dosyaAdi);
                $user->setImage($dosyaAdi);
            }
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,$id, User $user, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        /** @var User $user  */
        $user = $this->getUser(); // Get login User data
        if ($user->getId() != $id)
        {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $file = $form->get('image')->getData();

            if ($file) {

                $hedefDizin = 'uploads/images';
                $dosyaAdi = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($hedefDizin, $dosyaAdi);
                $user->setImage($dosyaAdi);
            }
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/newcomment/{id}', name: 'user_new_comment', methods: ['GET','POST'])]
    public function newcomment(Request $request,$id, EntityManagerInterface $entityManager, User $user): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('comment', $submittedToken)) {

                $comment->setStatus('New');
                $comment->setIp($_SERVER['REMOTE_ADDR']);
                $comment->setHotelid($id);
                /** @var User $user  */
                $user = $this->getUser();
                $comment->setUserid($user->getId());

                $entityManager->persist($comment);
                $entityManager->flush();

                $this->addFlash('success', 'Your comment has been sent successfuly');
                return $this->redirectToRoute('hotel_show', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('hotel_show', ['id'=> $id]);
    }

}
