<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/image')]
class ImageController extends AbstractController
{
    #[Route('/', name: 'app_user_image_index', methods: ['GET'])]
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('image/index.html.twig', [
            'images' => $imageRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_user_image_new', methods: ['GET', 'POST'])]
    public function new(Request $request,$id, ImageRepository $imageRepository): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('image')->getData();

            if ($file) {

                $hedefDizin = 'uploads/images';
                $dosyaAdi = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($hedefDizin, $dosyaAdi);
                $image->setImage($dosyaAdi);
            }
            $imageRepository->save($image, true);

            return $this->redirectToRoute('app_user_image_new', ['id'=> $id], Response::HTTP_SEE_OTHER);
        }

        return $this->render('image/new.html.twig', [
            'image' => $image,
            'form' => $form,
            'id'=>$id,
            'images' => $imageRepository->findBy(['hotel'=>$id]),
        ]);
    }

    #[Route('/{id}', name: 'app_user_image_show', methods: ['GET'])]
    public function show(Image $image): Response
    {
        return $this->render('image/show.html.twig', [
            'image' => $image,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_image_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Image $image, ImageRepository $imageRepository): Response
    {
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageRepository->save($image, true);

            return $this->redirectToRoute('app_user_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('image/edit.html.twig', [
            'image' => $image,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/{hid}', name: 'app_user_image_delete', methods: ['POST'])]
    public function delete(Request $request,$hid, Image $image, ImageRepository $imageRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $imageRepository->remove($image, true);
        }

        return $this->redirectToRoute('app_user_image_new', ['id'=> $hid], Response::HTTP_SEE_OTHER);
    }
}
