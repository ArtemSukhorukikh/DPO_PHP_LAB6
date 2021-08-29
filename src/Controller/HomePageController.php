<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    #[Route('/', name: 'home_page')]
    public function index(BookRepository $booksRepository): Response
    {
        return $this->render('home_page/index.html.twig', [
            'controller_name' => 'HomePageController',
            'books' => $booksRepository->findAll(),
        ]);
    }
}
