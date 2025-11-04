<?php

namespace App\Controller;

use App\Repository\SubjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


#[Route('/subjects', name: 'subject_')]
final class SubjectController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(SubjectRepository $repo): Response
    {
        return $this->render('subject/index.html.twig', [
            'studying' => $repo->findStudying(),
            'done'     => $repo->findDone(),
        ]);
    }
}
