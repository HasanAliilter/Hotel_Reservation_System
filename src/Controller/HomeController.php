<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Repository\HotelRepository;
use App\Repository\Admin\SettingRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(SettingRepository $settingRepository,HotelRepository $hotelRepository): Response
    {
        $data = $settingRepository->findOneBy(['id' => 1]);
        $slider=$hotelRepository->findBy(['status'=>'True'],['title'=>'ASC'] ,4);
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'data' => $data,
            'slider'=>$slider
        ]);
    }
    
    #[Route('/hotel/{id}', name: 'hotel_show', methods: ['GET'])]
    public function show(Hotel $hotel,): Response
    {
        return $this->render('home/hotelShow.html.twig', [
            'hotel' => $hotel,
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

    #[Route('/contact', name: 'home_contact', methods: ['GET',"POST"])]
    public function contact(SettingRepository $settingRepository): Response
    {
        $setting=$settingRepository->findAll();
        return $this->render('home/contact.html.twig', [
            'setting'=>$setting,
        ]);
    }
}
