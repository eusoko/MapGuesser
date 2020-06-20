<?php namespace MapGuesser\Repository;

use MapGuesser\Database\Query\Select;
use MapGuesser\PersistentData\Model\User;
use MapGuesser\PersistentData\PersistentDataManager;

class UserRepository
{
    private PersistentDataManager $pdm;

    public function __construct()
    {
        $this->pdm = new PersistentDataManager();
    }

    public function getById(int $userId): ?User
    {
        return $this->pdm->selectFromDbById($userId, User::class);
    }

    public function getByEmail(string $email): ?User
    {
        $select = new Select(\Container::$dbConnection);
        $select->where('email', '=', $email);

        return $this->pdm->selectFromDb($select, User::class);
    }
}
