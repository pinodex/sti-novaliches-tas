<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Sso;

class AccessToken
{
    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $code;

    /**
     * Constructs AccessToken
     * 
     * @var string $authorizationString Authorization header string value
     */
    public function __construct($authorizationString)
    {
        $authorization = explode(' ', $authorizationString, 2);

        if ($authorization[0] !== 'SSO-ACCESS-TOKEN') {
            throw new TokenAuthorizationException();
        }

        $params = [];

        parse_str(str_replace(',', '&', $authorization[1]), $params);

        if (!isset($params['id'], $params['secret'], $params['code'])) {
            throw new TokenAuthorizationException();
        }

        $this->clientId = $params['id'];
        $this->clientSecret = $params['secret'];
        $this->code = $params['code'];
    }

    /**
     * Get client ID
     * 
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Get client secret
     * 
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Get client token code
     * 
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
