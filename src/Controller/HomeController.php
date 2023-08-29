<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Entity\Admin\Messages;
use App\Form\Admin\MessagesType;
use App\Repository\HotelRepository;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Admin\SettingRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(SettingRepository $settingRepository,HotelRepository $hotelRepository): Response
    {
        $setting=$settingRepository->findAll();
        $slider=$hotelRepository->findBy(['status'=>'True'],['title'=>'ASC'] ,4);
        $hotels=$hotelRepository->findBy(['status'=>'True'],['title'=>'DESC'] ,4);
        $newhotels=$hotelRepository->findBy(['status'=>'True'],['title'=>'DESC'] ,10);
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'setting' => $setting,
            'slider'=>$slider,
            'hotels'=>$hotels,
            'newhotels'=>$newhotels,
        ]);
    }
    
    #[Route('/hotel/{id}', name: 'hotel_show', methods: ['GET'])]
    public function show(Hotel $hotel,$id,ImageRepository $imageRepository): Response
    {
        $images=$imageRepository->findBy(['hotel'=>$id]);

        return $this->render('home/hotelShow.html.twig', [
            'hotel' => $hotel,
            'images' => $images,
        ]);
    }

    #[Route('/about', name: 'home_about')]
    public function about(SettingRepository $settingRepository): Response
    {
        $setting=$settingRepository->findAll();
        return $this->render('home/aboutUs.html.twig', [
            'setting'=>$setting,

        ]);
    }
    
    #[Route('/contact', name: 'home_contact', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SettingRepository $settingRepository): Response
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token');
        $setting=$settingRepository->findAll();

        if ($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('form-message', $submittedToken)) {
                $message->setStatus('New');
                $message->setIp($_SERVER['REMOTE_ADDR']);
                $entityManager->persist($message);
                $entityManager->flush();
                $this->addFlash('success', 'Your message has been sent successfuly');

                return $this->redirectToRoute('home_contact', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('home/contact.html.twig', [
            'form' => $form,
            'setting'=>$setting,
        ]);
    }
}
