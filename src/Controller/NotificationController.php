<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


class NotificationController extends AbstractController
{
    // This function returns the latest notifications from database
    #[Route('/notifications/{mag_id}/{user_id}/{role_id}', name: 'get_notifications', methods: ['GET'])]
    public function getNotifications($mag_id, $user_id, $role_id, NotificationRepository $notificationRepository, SerializerInterface $serializer): JsonResponse
    {
        if($role_id === "1")
        {
            $notifications = $notificationRepository->findBy(['isRead' => false], ['createdAt' => 'DESC']);
        }
        elseif($role_id === "4")
        {
            $notifications = $notificationRepository->getNotification($mag_id, $user_id);
        }

        $jsonContent = $serializer->serialize($notifications, 'json', [AbstractNormalizer::GROUPS => ['notification']]);
    
        return new JsonResponse($jsonContent, 200, [], true);
    }


    // This function updates the is_read status for a specific notification
    #[Route('/notifications/read/{id}', name: 'mark_notification_read', methods: ['POST'])]
    public function markNotificationRead($id, NotificationRepository $notificationRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // Fetch the notification by ID
        $notification = $notificationRepository->find($id);

        if (!$notification) {
            return new JsonResponse(['error' => 'Notification not found'], 404);
        }

        // Mark the notification as read (set 'is_read' to 1)
        $notification->setIsRead(1); // true or 1 depending on your database configuration

        // Persist the change to the database
        $entityManager->persist($notification);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

}
