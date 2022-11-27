<?php 
namespace App\Libraries\LaravelOauth;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Exceptions\UnauthorizedException;

class Service implements Contract 
{
    /** @var Array $config */
    private $config;

    /** @var array $session */
    private array $session;

    /** @var string $apiBaseUrl */
    private string $apiBaseUrl;

    public function __construct(Array $config)
    {
        $this->config = $config;
        $apiBaseUrl = Arr::get($this->config,'api_base_url', '');
        if ($apiBaseUrl == '') {
            throw new \Exception('Laravel oauth api base url was not set');
        }
    
        $this->session = [];
        $this->apiBaseUrl = $apiBaseUrl;
    }

    /**
     * getAuthorizationPageUri
     * 
     * Generate authorization page uri 
     * @return string
     */
    public function getAuthorizationPageUri(): string
    {
        request()->session()->put('state', $state = Str::random(40));
        $query = http_build_query(Array(
            'client_id' => Arr::get($this->config, 'client_id', ''),
            'redirect_uri' => 'http://localhost:83/oauth/callback',
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ));

        return sprintf('%s/oauth/authorize', $this->apiBaseUrl). '?' . $query;
    }

    /**
     * retrieveToken
     */
    public function retrieveToken(string $authCode)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->asForm()->post(sprintf('%s/oauth/token', $this->apiBaseUrl), [
            'grant_type' => 'authorization_code',
            'client_id' => Arr::get($this->config, 'client_id', ''),
            'client_secret' => Arr::get($this->config, 'client_secret', ''),
            'redirect_uri' => 'http://localhost:83/oauth/callback',
            'code' => $authCode,
        ]);

        if ($response->getStatusCode() >= 300) {
            throw new UnauthorizedException("Failed to get oauth token");
        }

        $result = json_decode($response->body(), true);
        $this->session = [
            'access_token' => Arr::get($result, 'access_token', ''),
            'refresh_token' => Arr::get($result, 'refresh_token', ''),
        ];

        $this->getMe();
        
        try {
            $this->session['me'] = $this->getMe();
        } catch (\Exception $e) {
            throw new UnauthorizedException("Failed to get actor profile");
        }
    }

    /**
     * refreshToken
     */
    private function refreshToken()
    {
        $response = Http::asForm()->post(sprintf('%s/oauth/token', $this->apiBaseUrl), [
            'client_id' => Arr::get($this->config, 'client_id', ''),
            'client_secret' => Arr::get($this->config, 'client_secret', ''),
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->session['refresh_token'],
        ]);

        if ($response->getStatusCode() >= 300) {
            throw new UnauthorizedException("Failed to refresh token");
        }

        $result = json_decode($response->body(), true);
        $this->session['access_token'] = Arr::get($result, 'access_token', '');
        $this->session['refresh_token'] = Arr::get($result, 'refresh_token', '');
    }

    public function getMe(): array
    {
        $httpCallable = function() {
            return Http::withHeaders([
                'Authorization' => sprintf("Bearer %s", $this->session['access_token']),
            ])->get(sprintf('%s/api/user', $this->apiBaseUrl)); 
        };

        // Call api and retry once token is expired
        $response = $httpCallable();
        if ($response->getStatusCode() == 401) {
            $this->refreshToken();
            $response = $httpCallable();
        }

        if ($response->getStatusCode() >= 300) {
            throw new UnauthorizedException("Failed to get me");
        }

        $respBody = json_decode($response->body(), true);
        dd($respBody);

        $result = [
            'email' => Arr::get($respBody, 'email', ''),
            'name' => Arr::get($respBody, 'name', ''),
        ];

        return $result;
    }

    public function getToken(): array {
        return $this->session;
    }
}