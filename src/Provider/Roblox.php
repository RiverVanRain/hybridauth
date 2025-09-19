<?php

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\InvalidApplicationCredentialsException;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User\Profile;
use GuzzleHttp\Client;

class Roblox extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'openid profile email';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://create.roblox.com/docs/cloud/auth/oauth2-overview';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->apiBaseUrl = 'https://apis.roblox.com/oauth';
        $this->authorizeUrl = $this->apiBaseUrl . '/v1/authorize';
        $this->accessTokenUrl = $this->apiBaseUrl . '/v1/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        parent::initialize();

        if ($this->isRefreshTokenAvailable()) {
            $this->tokenRefreshParameters += [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->getStoredData('/v1/userinfo');
        if (!$response) {
            $response = $this->apiRequest('/v1/userinfo');
            $this->storeData('/v1/userinfo', $response);
        }

        $data = new Data\Collection($response);

        if (!$data->exists('sub')) {
            $this->deleteStoredData('/v1/userinfo');
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('sub');
        $userProfile->displayName = $data->get('preferred_username');
        $userProfile->email = $data->get('email');
        $userProfile->firstName = $data->get('name') ?? $data->get('nickname');
        $userProfile->emailVerified = $data->get('email_verified');

        $userProfile->photoURL = $data->get('picture');
        $userProfile->profileURL = $data->get('profile');

        return $userProfile;
    }

    public function sendMessage($accessToken, $universeId, $topic, $message)
    {
        $client = new Client();

        $url = "https://apis.roblox.com/messaging-service/v1/universes/$universeId/topics/$topic";

        $response = $client->post($url, [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode(['message' => $message])
        ]);

        return [
            'status' => $response->getStatusCode(),
            'body' => json_decode($response->getBody(), true)
        ];
    }
}
