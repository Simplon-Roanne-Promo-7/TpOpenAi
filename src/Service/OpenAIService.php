<?php

namespace App\Service;

use OpenAI;

class OpenAIService
{
    public static function chat(array $lastTenMessages)
    {
        $yourApiKey = 'your-api-key-here';
        if ($yourApiKey = 'your-api-key-here') {
            throw new \Exception('You need to set your API key in src/Service/OpenAIService.php');
        }

        $client = OpenAI::client($yourApiKey);

        $messages = [];
        foreach ($lastTenMessages as $message) {
            $messages[] = [
                'role' => $message->getRole(),
                'content' => $message->getContent(),

            ];
        }

        $result = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
        ]);

        return $result['choices'][0]['message'];
    }
}
