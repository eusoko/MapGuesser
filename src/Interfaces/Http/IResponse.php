<?php namespace MapGuesser\Interfaces\Http;

interface IResponse
{
    public function getBody();

    public function getHeaders();
}
