<?php namespace MapGuesser\Repository;

use MapGuesser\Database\Query\Select;
use MapGuesser\PersistentData\Model\UserConfirmation;
use MapGuesser\PersistentData\PersistentDataManager;

class UserConfirmationRepository
{
    private PersistentDataManager $pdm;

    public function __construct()
    {
        $this->pdm = new PersistentDataManager();
    }

    public function getById(int $userConfirmationId): ?UserConfirmation
    {
        return $this->pdm->selectFromDbById($userConfirmationId, UserConfirmation::class);
    }

    public function getByToken(string $token): ?UserConfirmation
    {
        $select = new Select(\Container::$dbConnection);
        $select->where('token', '=', $token);

        return $this->pdm->selectFromDb($select, UserConfirmation::class);
    }
}
