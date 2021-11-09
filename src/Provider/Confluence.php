<?php
/*!
* Hybridauth
* https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
*  (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
*/

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;

/**
 * Set up your OAuth2 at https://developer.atlassian.com/console/myapps{App-ID}/authorization/auth-code-grant
 */

/**
 * Confluence OAuth2 provider adapter.
 */
class Confluence extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'read:me';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.atlassian.com/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://auth.atlassian.com/authorize?audience=api.atlassian.com&response_type=code&prompt=consent';
	
    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://auth.atlassian.com/oauth/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developer.atlassian.com/cloud/confluence/oauth-2-3lo-apps/';
	
    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('me');
		
        $data = new Data\Collection($response);

        if (!$data->exists('account_id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('account_id');
		$userProfile->data = [
			'username' => str_replace(' ', '', $data->get('nickname')),
        ];
		$userProfile->email = $data->get('email');
        $userProfile->displayName = $data->get('name');
		$userProfile->photoURL = $data->get('picture');
		$userProfile->region = $data->get('zoneinfo');

        return $userProfile;
    }
}
