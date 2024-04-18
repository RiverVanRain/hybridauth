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
    protected $scope = 'read write';

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
        $userProfile->photoURL = $data->get('avatar') ?: $data->get('avatar_static');
		$userProfile->webSiteURL = $data->get('url');
		$userProfile->description = $data->get('note');
		$userProfile->firstName = $data->get('display_name');
		
		return $userProfile;
    }
	
	
	public function setUserStatus($status)
    {
        // Prepare request parameters.
        $params = [];
        if (isset($status['message'])) {
            $params['status'] = $status['message'];
        }
		
		$ids = [];
		
		if (isset($status['picture'])) {
			$headers = [
				'Content-Type' => 'multipart/form-data',
			];
			
			$pictures = $status['picture'];
			
			if (!is_array($pictures)) {
				$pictures = [$pictures];
			}
			
			foreach ($pictures as $picture) {
				$images = $this->apiRequest($this->config->get('url') . '/api/v2/media', 'POST', [
					'file' => new \CurlFile($picture, 'image/jpg', 'filename'),
				], $headers, true);
			   
				array_push($ids, $images->id);
			}
        }
		
		if (isset($status['video'])) {
            $headers = [
				'Content-Type' => 'multipart/form-data',
			];
	
			$videos = $this->apiRequest($this->config->get('url') . '/api/v2/media', 'POST', [
                'file' => new \CurlFile($status['video'], 'video/mp4', 'filename'),
            ], $headers, true);
			
			sleep(5);
			
			$state = $this->apiRequest($this->config->get('url') . '/api/v1/media/' . urlencode($videos->id), 'GET');
			
			if (isset($state->url)) {
				array_push($ids, $videos->id);
			}
        }
		
		if (!empty($ids)) {
			$ids = array_slice($ids, 0, 4);
			
			$params['media_ids'] = $ids; 
		}
		
		$headers = [
			'Content-Type' => 'application/json',
		];

        $response = $this->apiRequest('statuses', 'POST', $params, $headers, false);

        return $response;
    }
}
