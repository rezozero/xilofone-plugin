<?php

declare(strict_types=1);

namespace RezoZero\Xilofone\Composer;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use RezoZero\Xilofone\Composer\Model\File;
use RezoZero\Xilofone\Composer\Model\Project;
use RezoZero\Xilofone\Composer\Model\TranslatedFile;

final class XilofoneFileProvider
{
    private string $username;
    private string $password;
    private string $host;
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private UriFactoryInterface $uriFactory;
    private StreamFactoryInterface $streamFactory;
    private ?string $accessToken = null;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        string $username,
        #[\SensitiveParameter] string $password,
        string $host = 'https://xilofone.rezo-zero.com'
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->host = $host;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
    }

    private function getAccessToken(): string
    {
        if (null === $this->accessToken) {
            $params = [
                'username' => $this->username,
                'password' => $this->password
            ];
            $request = $this->requestFactory
                ->createRequest('POST', $this->host.'/api/token')
                ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
                ->withHeader('Accept', 'application/json')
                ->withBody($this->streamFactory->createStream(\http_build_query($params)))
            ;
            $responseArray = $this->sendJsonXilofoneRequest($request);
            if (!isset($responseArray['token']) || !\is_string($responseArray['token'])) {
                throw new \RuntimeException('Xilofone bad token response');
            }
            $this->accessToken = $responseArray['token'];
        }
        return $this->accessToken;
    }

    private function getXilofoneProject(string $projectId): Project
    {
        if (!\str_starts_with($projectId, '/api/projects/')) {
            throw new \RuntimeException('Project id is not a valid Xilofone project IRI');
        }
        $request = $this->requestFactory
            ->createRequest('GET', $this->host.$projectId)
            ->withHeader('Accept', 'application/ld+json')
            ->withHeader('Authorization', 'Bearer '.$this->getAccessToken())
        ;
        $responseArray = $this->sendJsonXilofoneRequest($request);
        if (!isset($responseArray['@type']) || $responseArray['@type'] !== 'Project') {
            throw new \RuntimeException('Xilofone bad File response');
        }
        return Project::fromArray($responseArray);
    }

    /**
     * @return array<TranslatedFile>
     */
    public function getXilofoneTranslatedFiles(string $fileId, string $destinationFolder): array
    {
        $uri = $this->host.'/api/files/'.$fileId;
        if (\str_starts_with($fileId, '/api/files/')) {
            $uri = $this->host.$fileId;
        }
        $request = $this->requestFactory
            ->createRequest('GET', $uri)
            ->withHeader('Accept', 'application/ld+json')
            ->withHeader('Authorization', 'Bearer '.$this->getAccessToken())
        ;
        $responseArray = $this->sendJsonXilofoneRequest($request);
        if (!isset($responseArray['@type']) || $responseArray['@type'] !== 'File') {
            throw new \RuntimeException('Xilofone bad File response');
        }
        $file = File::fromArray($responseArray);
        $translatedFiles = [];
        $detailedProject = $this->getXilofoneProject($file->getProject()->getIri());

        foreach ($detailedProject->getLocales() as $locale) {
            // Add locales before file extension
            $name = \preg_replace('/(\.[a-z0-9]+)$/i', '.' . $locale .'$1', $file->getName());
            $translatedFiles[] = new TranslatedFile(
                $name,
                $destinationFolder.'/'.$name,
                $this->fetchXilofoneTranslatedMessages($fileId, $locale, 'xliff')
            );
        }

        return $translatedFiles;
    }

    public function storeTranslatedFiles(array $translatedFiles): void
    {
        foreach ($translatedFiles as $file) {
            if (!($file instanceof TranslatedFile)) {
                throw new \RuntimeException('File is not a TranslatedFile');
            }
            if (false === \file_put_contents($file->getPath(), $file->getContent())) {
                throw new \RuntimeException('Unable to write file '.$file->getPath());
            }
        }
    }

    private function fetchXilofoneTranslatedMessages(string $fileId, string $locale, string $format = 'xliff'): string
    {
        $uri = $this->host.'/download/files/'.$fileId.'/'.$locale.'/translated/'.$format;
        $request = $this->requestFactory
            ->createRequest('GET', $uri)
            ->withHeader('Accept', '*/*')
            ->withHeader('Authorization', 'Bearer '.$this->getAccessToken())
        ;
        $response = $this->sendXilofoneRequest($request);
        return $response->getBody()->getContents();
    }

    private function sendXilofoneRequest(RequestInterface $request): ResponseInterface
    {
        $response = $this->client->sendRequest($request);
        match ($response->getStatusCode()) {
            200 => null,
            401 => throw new \RuntimeException('Xilofone bad credentials'),
            403 => throw new \RuntimeException('Xilofone authorization error'),
            404 => throw new \RuntimeException('Xilofone resource not found'),
            default => throw new \RuntimeException('Xilofone server error'),
        };
        return $response;
    }

    private function sendJsonXilofoneRequest(RequestInterface $request): array
    {
        $response = $this->sendXilofoneRequest($request);
        return \json_decode($response->getBody()->getContents(), true);
    }
}
