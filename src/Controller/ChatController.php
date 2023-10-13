<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\OpenAIService;

class ChatController extends AbstractController
{
    public function __construct(
        private  EntityManagerInterface $entityManager,
        private MessageRepository $messageRepository
    ) {
    }

    #[Route('/', name: 'app_message_index', methods: ['GET', 'POST'])]
    public function index(): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        return $this->render('chat/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_message_new', methods: ['GET', 'POST'])]
    public function new(Request $request)
    {
        $message = new Message();
        $data = json_decode($request->getContent(), true);
        $content = $data['message']['content'];

        $message->setContent($content);
        $message->setRole('user');
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $lastTenMessages = $this->messageRepository->findLastTenMessages();

        $result = OpenAIService::chat($lastTenMessages);

        $message = new Message();
        $message->setContent($result['content']);
        $message->setRole($result['role']);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $this->json($message);
    }

    #[Route('/getmessages', name: 'app_message_get', methods: ['GET', 'POST'])]
    public function getMessages()
    {
        $messages = $this->messageRepository->findAll();

        return $this->json($messages);
    }

    #[Route('/clear', name: 'app_message_clear', methods: ['GET', 'POST'])]
    public function clear(MessageRepository $messageRepository, EntityManagerInterface $entityManager): Response
    {
        $messages = $messageRepository->findAll();
        foreach ($messages as $message) {
            $entityManager->remove($message);
        }
        $entityManager->flush();

        return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
    }
}
