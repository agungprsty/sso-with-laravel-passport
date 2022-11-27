<?php 
namespace App\Libraries\LaravelOauth;

interface Contract
{
    public function getAuthorizationPageUri(): string;
    public function retrieveToken(string $authCode);
    public function getMe(): array;
    public function getToken(): array;
}