<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Entity\Admin\Room;
use App\Form\Admin\RoomType;
use App\Repository\HotelRepository;
use App\Repository\ImageRepository;
use App\Repository\Admin\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/room')]
class RoomController extends AbstractController
{
    #[Route('/', name: 'app_admin_room_index', methods: ['GET'])]
    public function index(RoomRepository $roomRepository): Response
    {
        return $this->render('admin/room/index.html.twig', [
            'rooms' => $roomRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_admin_room_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, $id, HotelRepository $hotelRepository, RoomRepository $roomRepository): Response
    {
        $rooms=$roomRepository->findBy(['hotelid'=>$id]);
        $hotel=$hotelRepository->findOneBy(['id'=>$id]);
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('image')->getData();

            if ($file) {

                $hedefDizin = 'uploads/images';
                $dosyaAdi = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($hedefDizin, $dosyaAdi);
                $room->setImage($dosyaAdi);
            }
            $roomRepository->save($room, true);
            $room->setHotelid($hotel->getId());
            $entityManager->persist($room);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_room_new', ['id'=> $id], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/room/new.html.twig', [
            'hotel' => $hotel,
            'room' => $room,
            'rooms' => $rooms,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_room_show', methods: ['GET'])]
    public function show(Room $room): Response
    {
        return $this->render('admin/room/show.html.twig', [
            'room' => $room,
        ]);
    }

    #[Route('/{id}/edit/{hid}', name: 'app_admin_room_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Room $room, $hid, EntityManagerInterface $entityManager, RoomRepository $roomRepository): Response
    {
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('image')->getData();

            if ($file) {

                $hedefDizin = 'uploads/images';
                $dosyaAdi = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($hedefDizin, $dosyaAdi);
                $room->setImage($dosyaAdi);
            }
            $roomRepository->save($room, true);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_room_new', ['id'=> $hid], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/room/edit.html.twig', [
            'room' => $room,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/{hid}', name: 'app_admin_room_delete', methods: ['POST'])]
    public function delete(Request $request, $hid,$id, Room $room, EntityManagerInterface $entityManager, HotelRepository $hotelRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {
            $entityManager->remove($room);
            $entityManager->flush();
        }
        $hotel=$hotelRepository->findOneBy(['id'=>$id]);

        return $this->redirectToRoute('app_admin_room_new', ['id' => $hid], Response::HTTP_SEE_OTHER);
    }
}
