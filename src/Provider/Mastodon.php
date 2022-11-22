<?php

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\InvalidApplicationCredentialsException;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User\Profile;

class Mastodon extends OAuth2
{

    /**
     * {@inheritdoc}
     */
    public $scope = 'read';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://docs.joinmastodon.org/spec/oauth/';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        if (!$this->config->exists('url')) {
            throw new InvalidApplicationCredentialsException(
                'You must define a Mastodon instance url'
            );
        }
        $url = $this->config->get('url');

        $this->apiBaseUrl = $url . '/api/v1';

        $this->authorizeUrl = $url . '/oauth/authorize';
        $this->accessTokenUrl = $url . '/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
		$response = $this->apiRequest('accounts/verify_credentials', 'GET', []);

        $data = new Data\Collection($response);
		
        if (!$data->exists('id') || !$data->get('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new Profile();

        $userProfile->identifier = $data->get('id');
        $userProfile->displayName = $data->get('username');
        $userProfile->firstName = $data->get('display_name');
        $userProfile->photoURL = $data->get('avatar') ?: $data->get('avatar_static');
		$userProfile->profileURL = $data->get('url');
		$userProfile->description = $data->get('note');
		
		return $userProfile;
    }
}
