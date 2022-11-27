<?php
namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException as BaseException;

class HttpException extends BaseException
{
    /** @var $code */
    protected $code;

    /** @var $message */
    protected $message;

    /** @var $errors */
    protected $meta = [];

    /**
     * Exception constructor
     *
     * @param $code
     * @param $message
     * @param \Exception|null $previous
     * @param array $headers
     */
    public function __construct($code, $message, \Exception $previous = null, array $headers = array())
    {
        $this->code = $code;
        $this->message = $message;

        parent::__construct($code, $message, $previous, $headers);
    }

    /**
     * Set exception array error
     * 
     * @param $meta
     * @return HttpException
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * Return meta variable
     * 
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
