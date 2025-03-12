<?php

/*!
* Hybridauth
* https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
*  (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
*/

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth1;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;

/**
 * Set up your OAuth2 at https://trello.com/app-key
 */

/**
 * Trello OAuth2 provider adapter.
 */
class Trello extends OAuth1
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'read, write, account';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.trello.com/1/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://trello.com/1/OAuthAuthorizeToken';

     /**
     * {@inheritdoc}
     */
    protected $requestTokenUrl = 'https://trello.com/1/OAuthGetRequestToken';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://trello.com/1/OAuthGetAccessToken';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developer.atlassian.com/cloud/trello/guides/rest-api/authorization/';

    protected function initialize()
    {
        parent::initialize();

        $this->AuthorizeUrlParameters += [
            'expiration' => 'never',
            'response_type' => 'fragment',
            'name' => $this->config->get('name')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('members/me');

        $data = new Data\Collection($response);

        if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('id');
        $userProfile->profileURL = 'https://trello.com/' . $data->get('username') . '/';
        $userProfile->data = [
            'username' => $data->get('username'),
        ];
        $userProfile->displayName = $data->get('fullName') ?: $data->get('username');
        $userProfile->description = $data->get('bio');
        $userProfile->photoURL = $data->get('avatarUrl');

        return $userProfile;
    }
}
