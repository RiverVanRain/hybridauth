<?php

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Data;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\User;

/**
 * OpenStreetMap OAuth2 provider adapter.
 */
class OpenStreetMap extends OAuth2
{
    /**
     * {@inheritdoc}
     */
	protected $scope = 'read_prefs';

    /**
     * {@inheritdoc}
     */
	protected $apiBaseUrl = 'https://api.openstreetmap.org/api/0.6/';
    
    /**
     * {@inheritdoc}
     */
	protected $authorizeUrl = 'https://www.openstreetmap.org/oauth2/authorize';
    
    /**
     * {@inheritdoc}
     */
	protected $accessTokenUrl = 'https://www.openstreetmap.org/oauth2/token';
	
	/**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://wiki.openstreetmap.org/wiki/API_v0.6#URL_+_authentication';

    /**
     * {@inheritdoc}
     */
	public function getUserProfile()
    {
		$response = $this->apiRequest('user/details');

		$data = new Data\Collection($response);
		$userData = $data->get('osm')['user'];

		if (!$userData) {
			throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
		}

		$userProfile = new User\Profile();

		$userProfile->identifier = isset($userData['@attributes']['id']) ? $userData['@attributes']['id'] : null;
		$userProfile->displayName = isset($userData['@attributes']['display_name']) ? $userData['@attributes']['display_name'] : null;
		$userProfile->photoURL = isset($userData['img']['@attributes']['href']) ? $userData['img']['@attributes']['href'] : null;
		$userProfile->description = isset($userData['description']) ? $userData['description'] : null;

		return $userProfile;  
    }
}
