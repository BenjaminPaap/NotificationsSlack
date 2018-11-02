<?php

namespace Bpa\Notifications\Handler;

use Bpa\Notifications\Notification\MessageInterface;
use GuzzleHttp\Client;

/**
 * Handler for Stride
 */
class SlackHandler implements HandlerInterface
{
    const URL = 'https://hooks.slack.com/services/{room}';

    /**
     * @var Client
     */
    private $client;

    /**
     * SlackHandler constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param MessageInterface $message
     *
     * @return bool|void
     */
    public function notify(MessageInterface $message)
    {
        if (false === $message->getRoom() instanceof SlackRoom) {
            return false;
        }

        $body = json_encode($this->getContent($message));

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }

        $response = $this->client->request(
            'POST',
            strtr(self::URL, [
                '{room}' => $message->getRoom()->getIdentifier(),
            ]),
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => $body,
            ]
        );

        if ($response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param MessageInterface $message
     *
     * @return array
     */
    private function getContent(MessageInterface $message)
    {
        return [
            'text' => $message->getMessage(),
        ];
    }
}
