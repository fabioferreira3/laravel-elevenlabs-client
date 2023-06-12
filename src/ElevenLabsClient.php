<?php

namespace Georgehadjisavva\ElevenLabsClient;

use Exception;
use Georgehadjisavva\ElevenLabsClient\Enums\LatencyOptimizationEnum;
use Georgehadjisavva\ElevenLabsClient\Enums\VoicesEnum;
use Georgehadjisavva\ElevenLabsClient\Interfaces\ElevenLabsClientInterface;
use Georgehadjisavva\ElevenLabsClient\Responses\ErrorResponse;
use Georgehadjisavva\ElevenLabsClient\Responses\SuccessResponse;
use Georgehadjisavva\ElevenLabsClient\TextToSpeech\TextToSpeech;
use Georgehadjisavva\ElevenLabsClient\Voice\Voice;
use GuzzleHttp\Client;


class ElevenLabsClient implements ElevenLabsClientInterface
{
    protected $apiKey;

    protected $httpClient;

    const BASE_URL = 'https://api.elevenlabs.io/v1/';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;

        $this->httpClient = new Client([
            'base_uri' => self::BASE_URL,
            'headers' => [
                'xi-api-key' => $this->apiKey,
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }


    /**
     * Get the Voice Instance.
     *
     * @return Voice The Voice instance.
     */
    public function voices(): Voice
    {
        return new Voice($this);
    }

    /**
     * Get the TextToSpeech Instance.
     *
     * @return TextToSpeech The Voice instance.
     */
    public function textToSpeech()
    {
        return new TextToSpeech($this);
    }

    /**
     * Generate a voice based on the provided content.
     *
     * @param string $content The content for voice generation.
     * @param string $voice_id The ID of the voice to use (default: 21m00Tcm4TlvDq8ikWAM ).
     * @param bool $optimize_latency Whether to optimize for latency (default: 0 ).
     *
     * @return array status,message
     */
    public function generateVoice(string $content, string $voice_id = VoicesEnum::RACHEL, bool $optimize_latency = LatencyOptimizationEnum::DEFAULT): array
    {
        try {
            $response = $this->httpClient->post('text-to-speech/' . $voice_id, [
                'json' => [
                    'text' => $content,
                ],
            ]);

            $status = $response->getStatusCode();

            if ($status === 200) {
                return (new SuccessResponse($status, "Voice Succesfully Generated"))->getResponse();
            }
        } catch (Exception $e) {
            // Decode the JSON string into a PHP associative array
            $errorMessageException = json_encode($e->getMessage());
            $errorMessage          = (new ErrorResponse($e->getCode(), $errorMessageException))->getResponse();

            return $errorMessage;
        }
    }

    public function getModels()
    {
        try {
            $response = $this->httpClient->get('models');

            $data = json_decode($response->getBody(), true);
            dd($data);
            return $data['voices'] ?? [];
        } catch (Exception $e) {
            $errorMessageException = json_encode($e->getMessage());
            return (new ErrorResponse($e->getCode(), $errorMessageException))->getResponse();
        }
    }
}
