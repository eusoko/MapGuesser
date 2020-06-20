<?php namespace MapGuesser\PersistentData\Model;

class UserConfirmation extends Model
{
    protected static string $table = 'user_confirmations';

    protected static array $fields = ['user_id', 'token'];

    private ?User $user = null;

    private ?int $userId = null;

    private string $token = '';

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
