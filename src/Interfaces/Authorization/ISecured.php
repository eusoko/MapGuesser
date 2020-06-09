<?php namespace MapGuesser\Interfaces\Authorization;

interface ISecured
{
    public function authorize(): bool;
}
