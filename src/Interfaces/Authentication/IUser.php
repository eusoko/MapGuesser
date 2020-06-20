<?php namespace MapGuesser\Interfaces\Authentication;

interface IUser
{
    const PERMISSION_NORMAL = 0;

    const PERMISSION_ADMIN = 1;

    public function hasPermission(int $permission): bool;

    public function getUniqueId();

    public function getDisplayName(): string;

    public function checkPassword(string $password): bool;
}
