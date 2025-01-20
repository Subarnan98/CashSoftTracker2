<?php

namespace App\Controller;

use App\Entity\Fichier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CKEFileUploadController extends AbstractController
{
    #[Route('/upload/file', name: 'upload_file', methods: ['POST'])]
    public function uploadImage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $file = $request->files->get('upload');

        $allowedFileTypes = ['image/jpeg', 'image/png', 'application/pdf', 'text/plain', 'text/csv'];

        if ($file && !in_array($file->getMimeType(), $allowedFileTypes)) {
            return new JsonResponse(['message' => 'Type de fichier non autorisé'], Response::HTTP_BAD_REQUEST);
        }

        // We get temporary ids for uploaded files
        $tempId = $request->get('temp_id'); 

        if ($file && $file->isValid()) 
        {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';
            
            $filename = uniqid() . '.' . $file->guessExtension();
            
            $file->move($uploadsDir, $filename);
    
            $fichier = new Fichier();
            $fichier->setTempId($tempId);
            $fichier->setFilename($filename);
            
            $entityManager->persist($fichier);
            $entityManager->flush();
    
            $fileUrl = '/uploads/' . $filename;
    
            return new JsonResponse(['url' => $fileUrl]);
        }
    
        return new JsonResponse(['message' => 'Fichier invalide'], Response::HTTP_BAD_REQUEST);
    }
}
