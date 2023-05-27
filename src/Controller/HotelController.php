<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Hotel;
use App\Form\Hotel1Type;
use App\Repository\HotelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/hotel')]
class HotelController extends AbstractController
{
    #[Route('/', name: 'app_user_hotel_index', methods: ['GET'])]
    public function index(HotelRepository $hotelRepository): Response
    {
        /** @var User $user  */
        $user = $this->getUser();
        return $this->render('hotel/index.html.twig', [
            'hotels' => $hotelRepository->findBy(['userid'=>$user->getId()]),
        ]);
    }

    #[Route('/new', name: 'app_user_hotel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, HotelRepository $hotelRepository): Response
    {
        $hotel = new Hotel();
        $form = $this->createForm(Hotel1Type::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var User $user  */
            $user = $this->getUser();
            $hotel->setUserid($user->getId());

            $file = $form->get('image')->getData();

            if ($file) {

                $hedefDizin = 'uploads/images';
                $dosyaAdi = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($hedefDizin, $dosyaAdi);
                $hotel->setImage($dosyaAdi);
            }
            $hotel->setStatus(status:"New");
            $hotelRepository->save($hotel, true);

            return $this->redirectToRoute('app_user_hotel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hotel/new.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_hotel_show', methods: ['GET'])]
    public function show(Hotel $hotel,$id): Response
    {
        /** @var User $user  */
        $user = $this->getUser(); // Get login User data
        if ($user->getId() != $hotel->getUserid())
        {
            return $this->redirectToRoute('app_home');
        }
        return $this->render('hotel/show.html.twig', [
            'hotel' => $hotel,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_hotel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hotel $hotel, HotelRepository $hotelRepository): Response
    {
        /** @var User $user  */
        $user = $this->getUser(); // Get login User data
        if ($user->getId() != $hotel->getUserid())
        {
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(Hotel1Type::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('image')->getData();

            if ($file) {

                $hedefDizin = 'uploads/images';
                $dosyaAdi = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($hedefDizin, $dosyaAdi);
                $hotel->setImage($dosyaAdi);
            }
            $hotelRepository->save($hotel, true);

            return $this->redirectToRoute('app_user_hotel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hotel/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_hotel_delete', methods: ['POST'])]
    public function delete(Request $request, Hotel $hotel, HotelRepository $hotelRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hotel->getId(), $request->request->get('_token'))) {
            $hotelRepository->remove($hotel, true);
        }

        return $this->redirectToRoute('app_user_hotel_index', [], Response::HTTP_SEE_OTHER);
    }
}
