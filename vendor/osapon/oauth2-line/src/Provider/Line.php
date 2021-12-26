<?php

namespace Osapon\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Line extends AbstractProvider
{
    use BearerAuthorizationTrait;

    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    /**
     * @var array Default fields to be requested from the user profile.
     * @link https://devdocs.line.me/en/#getting-user-profiles
     */
    protected $defaultUserFields = [
        'userId',
        'displayName',
        'pictureUrl',
    ];
    /**
     * @var array Additional fields to be requested from the user profile.
     *            If set, these values will be included with the defaults.
     */
    protected $userFields = [];

    public function getBaseAuthorizationUrl()
    {
        return 'https://access.line.me/oauth2/v2.1/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://api.line.me/oauth2/v2.1/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $fields = array_merge($this->defaultUserFields, $this->userFields);
        return 'https://api.line.me/v2/profile?' . http_build_query([
            'fields' => implode(',', $fields),
            'alt'    => 'json',
        ]);
    }

    protected function getDefaultScopes()
    {
        return [
            'openid',
            'profile',
        ];
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $code  = 0;
            $error = $data['error'];

            if (is_array($error)) {
                $code  = $error['code'];
                $error = $error['message'];
            }

            throw new IdentityProviderException($error, $code, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new LineUser($response);
    }
}
