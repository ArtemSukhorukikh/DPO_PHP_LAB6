<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\FileUploader;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/book')]
class BookController extends AbstractController
{
    #[Route('/', name: 'book_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findBy([], ['readDate' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'book_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SluggerInterface $sluggerInterface): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('home_page');
        }
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverBookDirectory = $this->getParameter('bookCoverDirectory');
            $fileBookDirectoty = $this->getParameter('bookFileDirectory');
            $newBookCover = $form->get('cover')->getData();
            $newBookFile = $form->get('file')->getData();
            if ($newBookCover) {
                $fileUploader = new FileUploader($coverBookDirectory, $sluggerInterface);
                $newBookCoverPath = $fileUploader->upload($newBookCover);
                $book->setCover($newBookCoverPath);
            }
            if ($newBookFile) {
                $fileUploader = new FileUploader($fileBookDirectoty, $sluggerInterface);
                $newBookFilePath = $fileUploader->upload($newBookFile);
                $book->setFile($newBookFilePath);
            }
            $book->setUserRead($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'book_show', methods: ['GET'])]
    public function show(Book $book): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('home_page');
        }
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{id}/edit', name: 'book_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book, SluggerInterface $sluggerInterface): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('home_page');
        }
        if (!$this->getUser() || !($book->getUserRead() == $this->getUser())){
            return $this->redirectToRoute('book_index');
        }

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverBookDirectory = $this->getParameter('bookCoverDirectory');
            $fileBookDirectoty = $this->getParameter('bookFileDirectory');
            $newBookCover = $form->get('cover')->getData();
            $newBookFile = $form->get('file')->getData();
            $coverFileUploader = new FileUploader($coverBookDirectory, $sluggerInterface);
            $coverFileUploader->remove($book->getCover());
            $book->setCover('');
            $newBookCoverPath = $coverFileUploader->upload($newBookCover);
            $book->setCover($newBookCoverPath);
            $fileUploader = new FileUploader($fileBookDirectoty, $sluggerInterface);
            $fileUploader->remove($book->getFile());
            $book->setFile('');
            $newBookFilePath = $fileUploader->upload($newBookFile);
            $book->setFile($newBookFilePath);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('home_page');
        }
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('book_index', [], Response::HTTP_SEE_OTHER);
    }
}
