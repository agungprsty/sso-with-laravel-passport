<?php
namespace App\Exceptions;

class UnauthorizedException extends HttpException
{
    public function __construct($message = 'Unauthorized', \Exception $previous = null, array $headers = array())
    {
        parent::__construct(401, $message, $previous, $headers);
    }
}
