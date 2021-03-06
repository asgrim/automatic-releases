<?php

declare(strict_types=1);

namespace Laminas\AutomaticReleases\Github\Api\V3;

use Laminas\AutomaticReleases\Git\Value\BranchName;
use Laminas\AutomaticReleases\Github\Value\RepositoryName;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Webmozart\Assert\Assert;

use function Safe\json_decode;
use function Safe\json_encode;

final class CreatePullRequestThroughApiCall implements CreatePullRequest
{
    private const API_ROOT = 'https://api.github.com/';

    private RequestFactoryInterface $messageFactory;

    private ClientInterface $client;

    private string $apiToken;

    /** @psalm-param non-empty-string $apiToken */
    public function __construct(
        RequestFactoryInterface $messageFactory,
        ClientInterface $client,
        string $apiToken
    ) {
        $this->messageFactory = $messageFactory;
        $this->client         = $client;
        $this->apiToken       = $apiToken;
    }

    public function __invoke(
        RepositoryName $repository,
        BranchName $head,
        BranchName $target,
        string $title,
        string $body
    ): void {
        $request = $this->messageFactory
            ->createRequest(
                'POST',
                self::API_ROOT . 'repos/' . $repository->owner() . '/' . $repository->name() . '/pulls'
            )
            ->withAddedHeader('Content-Type', 'application/json')
            ->withAddedHeader('User-Agent', 'Ocramius\'s minimal API V3 client')
            ->withAddedHeader('Authorization', 'token ' . $this->apiToken);

        $request
            ->getBody()
            ->write(json_encode([
                'title'                 => $title,
                'head'                  => $head->name(),
                'base'                  => $target->name(),
                'body'                  => $body,
                'maintainer_can_modify' => true,
                'draft'                 => false,
            ]));

        $response = $this->client->sendRequest($request);

        $responseBody = $response
            ->getBody()
            ->__toString();

        Assert::greaterThanEq($response->getStatusCode(), 200);
        Assert::lessThanEq($response->getStatusCode(), 299);

        $responseData = json_decode($responseBody, true);

        Assert::isMap($responseData);
        Assert::keyExists($responseData, 'url');
    }
}
