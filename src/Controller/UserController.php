<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Admin\Comment;
use App\Form\Admin\CommentType;
use App\Entity\Admin\Reservation;
use App\Repository\UserRepository;
use App\Form\Admin\ReservationType;
use App\Repository\HotelRepository;
use App\Repository\Admin\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Admin\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\Admin\ReservationRepository;
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
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        /** @var User $user  */
        $user = $this->getUser();
        $reservations=$reservationRepository->getUserReservation($user->getId());
        return $this->render('user/reservations.html.twig', [
            'reservations' =>$reservations,
            ]);
    }

    #[Route('/reservation/{id}', name: 'user_reservation_show', methods: ['GET'])]
    public function reservationshow($id,ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $reservation=$reservationRepository->getReservation($id);
        return $this->render('user/reservation_show.html.twig', [
            'reservation' =>$reservation,
        ]);
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

    #[Route('/reservation/{hid}/{rid}', name: 'user_reservation_new', methods: ['GET','POST'])]
    public function newreservation(Request $request,$hid,$rid,HotelRepository $hotelRepository, RoomRepository $roomRepository, EntityManagerInterface $entityManager): Response
    {

        $hotel=$hotelRepository->findOneBy(['id'=>$hid]);
        $room=$roomRepository->findOneBy(['id'=>$rid]);

        $days=$_REQUEST["days"];
        $checkin=$_REQUEST["checkin"];
        $checkout= Date("Y-m-d H:i:s", strtotime($checkin ." $days Day"));
        $checkin= Date("Y-m-d H:i:s", strtotime($checkin ." 0 Day"));

        $data["total"]=$days * $room->getPrice();
        $data["days"]=$days;
        $data["checkin"]=$checkin;
        $data["checkout"]=$checkout;

        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);


        $submittedToken = $request->request->get('token');
        if ($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('form-reservation', $submittedToken)) {

                $checkin=date_create_from_format("Y-m-d H:i:s",$checkin); //Convert to datetime format
                $checkout=date_create_from_format("Y-m-d H:i:s",$checkout); //Convert to datetime format
                $reservation->setCheckin($checkin);
                $reservation->setCheckout($checkout);
                $reservation->setStatus('New');
                $reservation->setIp($_SERVER['REMOTE_ADDR']);
                $reservation->setHotelid($hid);
                $reservation->setRoomid($rid);
                /** @var User $user  */
                $user = $this->getUser();
                $reservation->setUserid($user->getId());
                $reservation->setDays($days);
                $reservation->setTotal($data["total"]);
                $reservation->setCreatedAt(new \DateTimeImmutable());

                $entityManager->persist($reservation);
                $entityManager->flush();

                return $this->redirectToRoute('app_user_reservations');
            }
        }


        return $this->render('user/newreservation.html.twig', [
            'reservation' => $reservation,
            'room' => $room,
            'hotel' => $hotel,
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }

}
