<?php

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\InvalidApplicationCredentialsException;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User\Profile;

class TikTok extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'user.info.basic';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://open.tiktokapis.com/v2/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://www.tiktok.com/v2/auth/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://open.tiktokapis.com/v2/oauth/token';

    /**
     * {@inheritdoc}
     */
    protected $refreshTokenUrl = 'https://open.tiktokapis.com/v2/oauth/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developers.tiktok.com/doc/overview/';

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        parent::initialize();

        $this->AuthorizeUrlParameters = [
            'response_type' => 'code',
            'client_key' => $this->clientId,
            'redirect_uri' => $this->callback,
            'scope' => $this->scope,
        ];

        $this->tokenExchangeParameters = [
            'client_key' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->callback,
        ];

        $this->tokenRefreshParameters = [
            'client_key' => $this->clientId,
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getStoredData('refresh_token'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('user/info/?fields=open_id,union_id,avatar_url,display_name,profile_deep_link');
        if (!property_exists($response, 'data') || !property_exists($response->data, 'user') || !property_exists($response->data->user, 'union_id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $data = new Data\Collection($response->data->user);

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('union_id');
        $userProfile->displayName = $data->get('display_name');
        $userProfile->description = $data->get('bio_description');
        $userProfile->profileURL = $data->get('profile_deep_link');
        $userProfile->photoURL = $data->get('avatar_url');

        return $userProfile;
    }
}
