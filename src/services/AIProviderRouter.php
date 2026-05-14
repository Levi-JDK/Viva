<?php

require_once __DIR__ . '/../exceptions/AIProviderException.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class AIProviderRouter
{
    private const OPENROUTER_CHAT_ENDPOINT = 'https://openrouter.ai/api/v1/chat/completions';
    private const OPENROUTER_EMBED_ENDPOINT = 'https://openrouter.ai/api/v1/embeddings';
    private const NVIDIA_ENDPOINT = 'https://integrate.api.nvidia.com/v1/chat/completions';
    private const NVIDIA_EMBED_ENDPOINT = 'https://integrate.api.nvidia.com/v1/embeddings';
    private const DEFAULT_MODEL = 'nvidia/llama-nemotron-embed-vl-1b-v2';
    private const DEFAULT_DECISION_MODEL = 'nvidia/nemotron-3-nano-omni-30b-a3b-reasoning';

    private static ?Client $client = null;

    /**
     * @param string $input
     * @param string $inputType
     * @return array{embedding: array<int,float>, provider: string, model: string, response_time: float}
     * @throws AIProviderException
     */
    public static function generateEmbedding(string $input, string $inputType = 'text'): array
    {
        $model = self::getModel();
        $payload = self::buildEmbedPayload($input, $inputType, $model);
        $providers = [self::getPrimaryProvider(), self::getSecondaryProvider()];
        $lastException = null;

        foreach (array_unique($providers) as $provider) {
            try {
                $start = microtime(true);
                $response = self::callProvider($provider, $payload, true);
                $elapsed = round(microtime(true) - $start, 4);
                $embedding = self::extractEmbeddingFromResponse($response);

                self::logObservation($provider, $elapsed, $model, 'success');

                return [
                    'embedding' => $embedding,
                    'provider' => $provider,
                    'model' => $model,
                    'response_time' => $elapsed,
                ];
            } catch (AIProviderException $exception) {
                $lastException = $exception;
                self::logObservation($provider, 0.0, $model, 'failure: ' . $exception->getMessage());
            }
        }

        throw $lastException ?? new AIProviderException('No se pudo generar el embedding.', 'unknown');
    }

    /**
     * @param string $text
     * @return array{embedding: array<int,float>, provider: string, model: string, response_time: float}
     * @throws AIProviderException
     */
    public static function generateTextEmbedding(string $text): array
    {
        return self::generateEmbedding($text, 'text');
    }

    /**
     * @param string $imageUrl
     * @return array{embedding: array<int,float>, provider: string, model: string, response_time: float}
     * @throws AIProviderException
     */
    public static function generateImageEmbedding(string $imageUrl): array
    {
        $processedUrl = $imageUrl;

        // If it's a local file path (not HTTP(s)), convert to base64 data URL
        if (!preg_match('/^https?:\/\//i', $imageUrl) && is_file($imageUrl) && is_readable($imageUrl)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $imageUrl);
            finfo_close($finfo);

            if (!$mimeType || !str_starts_with($mimeType, 'image/')) {
                throw new AIProviderException("El archivo local no es una imagen válida: {$imageUrl}", 'input');
            }

            $base64 = base64_encode(file_get_contents($imageUrl));
            $processedUrl = "data:{$mimeType};base64,{$base64}";
        }

        return self::generateEmbedding($processedUrl, 'image');
    }

    /**
     * @param array<int,array{role:string,content:mixed}> $messages
     * @param string|null $model
     * @return array{content: string, provider: string, model: string, response_time: float}
     * @throws AIProviderException
     */
    /**
     * @param array<int,array{role:string,content:mixed}> $messages
     * @param string|null $model
     * @param array<string>|null $providerOrder
     * @return array{content: string, provider: string, model: string, response_time: float}
     * @throws AIProviderException
     */
    public static function callChat(array $messages, ?string $model = null, ?array $providerOrder = null): array
    {
        if ($messages === []) {
            throw new AIProviderException('Los mensajes para chat no pueden estar vacíos.', 'input');
        }

        foreach ($messages as $message) {
            if (!is_array($message) || empty($message['role']) || !array_key_exists('content', $message)) {
                throw new AIProviderException('Formato de mensaje inválido para chat.', 'input');
            }
        }

        $selectedModel = $model ?: self::getDecisionModel();
        $payload = [
            'model' => $selectedModel,
            'messages' => $messages,
            'temperature' => 0.1,
            'max_tokens' => 1500,
        ];
        $providers = $providerOrder ?? [self::getPrimaryProvider(), self::getSecondaryProvider()];
        $lastException = null;

        foreach (array_unique($providers) as $provider) {
            try {
                $start = microtime(true);
                $response = self::callProvider($provider, $payload);
                $elapsed = round(microtime(true) - $start, 4);
                $content = self::extractChatContentFromResponse($response);

                self::logObservation($provider, $elapsed, $selectedModel, 'chat_success');

                return [
                    'content' => $content,
                    'provider' => $provider,
                    'model' => $selectedModel,
                    'response_time' => $elapsed,
                ];
            } catch (AIProviderException $exception) {
                $lastException = $exception;
                self::logObservation($provider, 0.0, $selectedModel, 'chat_failure: ' . $exception->getMessage());
            }
        }

        throw $lastException ?? new AIProviderException('No se pudo completar el chat.', 'unknown');
    }

    /**
     * @param string $systemPrompt
     * @param string $userPrompt
     * @return array{content: string, provider: string, model: string, response_time: float, parsed: array}
     * @throws AIProviderException
     */
    public static function callDecisionModel(string $systemPrompt, string $userPrompt): array
    {
        // Decision model: NVIDIA first (OpenRouter doesn't serve nemotron-3-nano-omni)
        $response = self::callChat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ], self::getDecisionModel(), ['nvidia', 'openrouter']);
        $response['parsed'] = self::parseDecisionJson($response['content']);

        return $response;
    }

    /**
     * @param string $provider
     * @param array $payload
     * @param bool $isEmbedding
     * @return array
     * @throws AIProviderException
     */
    private static function callProvider(string $provider, array $payload, bool $isEmbedding = false): array
    {
        return match ($provider) {
            'openrouter' => $isEmbedding ? self::callOpenRouterEmbed($payload) : self::callOpenRouter($payload),
            'nvidia' => $isEmbedding ? self::callNvidiaEmbed($payload) : self::callNvidia($payload),
            default => throw new AIProviderException("Proveedor AI no soportado: {$provider}", $provider),
        };
    }

    /**
     * @param array $payload
     * @return array
     * @throws AIProviderException
     */
    private static function callOpenRouter(array $payload): array
    {
        $apiKey = (string) ($_ENV['OPENROUTER_API_KEY'] ?? '');
        if ($apiKey === '') {
            throw new AIProviderException('OPENROUTER_API_KEY no configurada.', 'openrouter');
        }

        return self::postJson('openrouter', self::OPENROUTER_CHAT_ENDPOINT, $apiKey, $payload, [
            'HTTP-Referer' => (string) ($_ENV['BASE_URL'] ?? 'http://localhost'),
            'X-Title' => 'Viva AI Product Validation',
        ]);
    }

    /**
     * @param array $payload
     * @return array
     * @throws AIProviderException
     */
    private static function callOpenRouterEmbed(array $payload): array
    {
        $apiKey = (string) ($_ENV['OPENROUTER_API_KEY'] ?? '');
        if ($apiKey === '') {
            throw new AIProviderException('OPENROUTER_API_KEY no configurada.', 'openrouter');
        }

        return self::postJson('openrouter', self::OPENROUTER_EMBED_ENDPOINT, $apiKey, $payload, [
            'HTTP-Referer' => (string) ($_ENV['BASE_URL'] ?? 'http://localhost'),
            'X-Title' => 'Viva AI Product Validation',
        ]);
    }

    /**
     * @param array $payload
     * @return array
     * @throws AIProviderException
     */
    private static function callNvidia(array $payload): array
    {
        $apiKey = (string) ($_ENV['NVIDIA_API_KEY'] ?? '');
        if ($apiKey === '') {
            throw new AIProviderException('NVIDIA_API_KEY no configurada.', 'nvidia');
        }

        return self::postJson('nvidia', self::NVIDIA_ENDPOINT, $apiKey, $payload);
    }

    /**
     * @param array $payload
     * @return array
     * @throws AIProviderException
     */
    private static function callNvidiaEmbed(array $payload): array
    {
        $apiKey = (string) ($_ENV['NVIDIA_API_KEY'] ?? '');
        if ($apiKey === '') {
            throw new AIProviderException('NVIDIA_API_KEY no configurada.', 'nvidia');
        }

        // Add required input_type for NVIDIA embeddings
        $payload['input_type'] = $payload['input_type'] ?? 'passage';

        return self::postJson('nvidia', self::NVIDIA_EMBED_ENDPOINT, $apiKey, $payload);
    }

    /**
     * @param string $provider
     * @param string $endpoint
     * @param string $apiKey
     * @param array $payload
     * @param array<string,string> $extraHeaders
     * @return array
     * @throws AIProviderException
     */
    private static function postJson(string $provider, string $endpoint, string $apiKey, array $payload, array $extraHeaders = []): array
    {
        try {
            $response = self::getClient()->post($endpoint, [
                'headers' => array_merge([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ], $extraHeaders),
                'json' => $payload,
                'timeout' => self::getTimeout(),
                'connect_timeout' => self::getTimeout(),
            ]);

            return self::decodeResponse($provider, $response);
        } catch (GuzzleException $exception) {
            $status = method_exists($exception, 'getResponse') && $exception->getResponse()
                ? $exception->getResponse()->getStatusCode()
                : 0;

            throw new AIProviderException($exception->getMessage(), $provider, $status, $exception);
        }
    }

    /**
     * @param string $provider
     * @param ResponseInterface $response
     * @return array
     * @throws AIProviderException
     */
    private static function decodeResponse(string $provider, ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            throw new AIProviderException('Respuesta HTTP inválida: ' . $body, $provider, $status);
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new AIProviderException('La respuesta del proveedor no es JSON válido.', $provider, $status);
        }

        return $decoded;
    }

    /**
     * @param array $response
     * @return array<int,float>
     * @throws AIProviderException
     */
    private static function extractEmbeddingFromResponse(array $response): array
    {
        // Embeddings API response: { "data": [ { "embedding": [...], "index": 0 } ], "model": "..." }
        $embedding = $response['data'][0]['embedding'] ?? null;

        if (is_array($embedding)) {
            $floats = [];
            foreach ($embedding as $value) {
                if (!is_int($value) && !is_float($value) && !is_numeric($value)) {
                    throw new AIProviderException('El vector contiene valores no numéricos.', 'response');
                }
                $floats[] = (float) $value;
            }
            return $floats;
        }

        throw new AIProviderException('No se pudo extraer el embedding de la respuesta.', 'response');
    }

    /**
     * @param array $response
     * @return string
     * @throws AIProviderException
     */
    private static function extractChatContentFromResponse(array $response): string
    {
        $content = $response['choices'][0]['message']['content'] ?? null;
        if (!is_string($content) || trim($content) === '') {
            throw new AIProviderException('La respuesta no contiene contenido de chat.', 'response');
        }

        return trim($content);
    }

    /**
     * @param string $content
     * @return array
     */
    private static function parseDecisionJson(string $content): array
    {
        $decoded = json_decode(trim($content), true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $content, $matches) === 1) {
            $decoded = json_decode(trim($matches[1]), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        if (preg_match('/\{[\s\S]*\}/', $content, $matches) === 1) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [
            'decision' => 'revision_humana',
            'artesanalidad' => [
                'status' => 'dudosa',
                'score' => 0.0,
                'reason' => 'La IA no devolvió JSON válido.',
            ],
            'motivo_general' => 'Respuesta no parseable del modelo de decisión.',
        ];
    }

    /**
     * @param string $input
     * @param string $inputType
     * @param string $model
     * @return array
     * @throws AIProviderException
     */
    private static function buildEmbedPayload(string $input, string $inputType, string $model): array
    {
        if (trim($input) === '') {
            throw new AIProviderException('El input para embedding no puede estar vacío.', 'input');
        }

        $payload = [
            'model' => $model,
            'input' => $input,
            'input_type' => 'passage',
            'encoding_format' => 'float',
        ];

        // Para texto, algunos modelos no soportan input_type
        // Se prueba con y sin según el provider
        return $payload;
    }

    /**
     * @return Client
     */
    private static function getClient(): Client
    {
        if (self::$client === null) {
            self::$client = new Client();
        }

        return self::$client;
    }

    /**
     * @return string
     */
    private static function getPrimaryProvider(): string
    {
        return strtolower((string) ($_ENV['AI_PRIMARY_PROVIDER'] ?? 'openrouter'));
    }

    /**
     * @return string
     */
    private static function getSecondaryProvider(): string
    {
        return strtolower((string) ($_ENV['AI_SECONDARY_PROVIDER'] ?? 'nvidia'));
    }

    /**
     * @return string
     */
    private static function getModel(): string
    {
        return (string) ($_ENV['AI_EMBEDDING_MODEL'] ?? self::DEFAULT_MODEL);
    }

    /**
     * @return string
     */
    private static function getDecisionModel(): string
    {
        return (string) ($_ENV['AI_DECISION_MODEL'] ?? self::DEFAULT_DECISION_MODEL);
    }

    /**
     * @return float
     */
    private static function getTimeout(): float
    {
        $timeout = (float) ($_ENV['AI_PROVIDER_TIMEOUT'] ?? 30);
        return $timeout > 0 ? $timeout : 30.0;
    }

    /**
     * @param string $provider
     * @param float $elapsed
     * @param string $model
     * @param string $status
     * @return void
     */
    private static function logObservation(string $provider, float $elapsed, string $model, string $status): void
    {
        error_log(sprintf(
            '[AIProviderRouter] provider=%s elapsed_ms=%.2f model=%s status=%s',
            $provider,
            $elapsed * 1000,
            $model,
            $status
        ));
    }
}
