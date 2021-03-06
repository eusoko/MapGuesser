<?php namespace MapGuesser\PersistentData\Model;

use MapGuesser\Interfaces\Authentication\IUser;

class User extends Model implements IUser
{
    protected static string $table = 'users';

    protected static array $fields = ['email', 'password', 'type', 'active', 'google_sub'];

    private static array $types = ['user', 'admin'];

    private string $email = '';

    private ?string $password = null;

    private string $type = 'user';

    private bool $active = false;

    private ?string $googleSub = null;

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(?string $hashedPassword): void
    {
        $this->password = $hashedPassword;
    }

    public function setPlainPassword(string $plainPassword): void
    {
        $this->password = password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    public function setType(string $type): void
    {
        if (in_array($type, self::$types)) {
            $this->type = $type;
        }
    }

    public function setActive($active): void
    {
        $this->active = (bool) $active;
    }

    public function setGoogleSub(?string $googleSub): void
    {
        $this->googleSub = $googleSub;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getGoogleSub(): ?string
    {
        return $this->googleSub;
    }

    public function hasPermission(int $permission): bool
    {
        switch ($permission) {
            case IUser::PERMISSION_NORMAL:
                return true;
                break;
            case IUser::PERMISSION_ADMIN:
                return $this->type === 'admin';
                break;
        }
    }

    public function getUniqueId()
    {
        return $this->id;
    }

    public function getDisplayName(): string
    {
        return $this->email;
    }

    public function checkPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}
