<?php

namespace App\Controller;

use App\Entity\Fichier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TinyMCEFileUploadController extends AbstractController
{
    #[Route('/file/upload', name: 'file_upload', methods: ['POST'])]
    public function uploadFile(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $file = $request->files->get('upload');

        if (!$file) {
            return new JsonResponse(['error' => 'Aucun fichier téléchargé'], Response::HTTP_BAD_REQUEST);
        }

        // Validate file types
        $allowedMimeTypes = [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf', 'text/plain', 'text/csv',
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse(['error' => 'Type de fichier non valide'], Response::HTTP_BAD_REQUEST);
        }

        // We get temporary ids for uploaded files
        $tempId = $request->get('temp_id'); 

        if ($file && $file->isValid()) 
        {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';

            // Use the original extension if available, otherwise use the guessed extension
            $originalExtension = $file->getClientOriginalExtension();
            
            $extension = $originalExtension ?: $file->guessExtension();

            // Ensure the correct extension for CSV files
            if ($file->getMimeType() === 'text/csv' && $extension !== 'csv') {
                $extension = 'csv';
            }
            
            $filename = uniqid() . '.' . $extension;
            
            try {
                $file->move($uploadsDir, $filename);
    
                $fichier = new Fichier();
                $fichier->setTempId($tempId);
                $fichier->setFilename($filename);
                
                $entityManager->persist($fichier);
                $entityManager->flush();
        
                return new JsonResponse(['location' => '/uploads/' . $filename,]);
            }
            catch (\Exception $e) {
                return new JsonResponse(['error' => 'Le téléchargement du fichier a échoué'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            
        }
    
        return new JsonResponse(['message' => 'Fichier invalide'], Response::HTTP_BAD_REQUEST);
    }
}
