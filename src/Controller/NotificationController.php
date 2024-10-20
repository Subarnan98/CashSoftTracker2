<?php

namespace App\Controller;

use App\Entity\NotificationUser;
use App\Repository\NotificationUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


class NotificationController extends AbstractController
{
    // This function returns the latest notifications from database
    #[Route('/notifications/{user_id}', name: 'get_notifications', methods: ['GET'])]
    public function getNotifications($user_id, NotificationUserRepository $notificationUserRepository, SerializerInterface $serializer): JsonResponse
    {
        $notifications = $notificationUserRepository->findNotificationsByUserId($user_id);

        $jsonContent = $serializer->serialize($notifications, 'json', [AbstractNormalizer::GROUPS => ['notification']]);
    
        return new JsonResponse($jsonContent, 200, [], true);
    }


    // This function sets is_read = 1 for a specific notification inside "notification_user" table
    #[Route('/notifications/read/{id}', name: 'set_notification_read', methods: ['POST'])]
    public function setNotificationAsRead(NotificationUser $notificationUser, EntityManagerInterface $entityManager): JsonResponse
    {
        $notificationUser->setIsRead(1); // true or 1 depending on your database configuration

        $entityManager->persist($notificationUser);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }


    // This function returns the latest notifications from database
    #[Route('/notifications/delete/{user_id}/{role}', name: 'delete_all_notifications', methods: ['GET'])]
    public function deleteAllNotifications($user_id, $role, NotificationUserRepository $notificationUserRepository, EntityManagerInterface $entityManager)
    {
        $allNotifications = $notificationUserRepository->findBy(['user' => $user_id]);

        if(count($allNotifications) > 0)
        {
            foreach($allNotifications as $notification)
            {
                $notification->setIsRead(1);
                $entityManager->persist($notification);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute($role.'.index');
    }

}
