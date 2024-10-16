<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'home.index')]
    public function index():Response
    {
        // This will direct us to login page 
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if($this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('admin.index');
        }
        elseif($this->isGranted('ROLE_USER'))
        {

            return $this->redirectToRoute('user.index');
        }
    }
}