<?php

declare(strict_types=1);

namespace App\Auth;

use App\Entity\User;
use Symfony\Component\Yaml\Yaml;

final readonly class Permissions
{
    /** @var array<mixed, mixed> */
    public array $data;

    public function __construct(
        string $baseDir,
        string $path,
    ) {
        $data = Yaml::parseFile($baseDir.DIRECTORY_SEPARATOR.$path);

        assert(is_array($data));
        assert(array_key_exists('permissions', $data));
        assert(is_array($data['permissions']));

        $this->data = $data['permissions'];
    }

    public function hasPermission(User $user, string $permission): bool
    {
        foreach ($user->getRoles() as $role) {
            if ($this->doHasPermission($role, $permission)) {
                return true;
            }
        }

        return false;
    }

    private function doHasPermission(string $role, string $permission): bool
    {
        if (!array_key_exists($permission, $this->data)) {
            return false;
        }

        assert(is_array($this->data[$permission]));

        return in_array($role, $this->data[$permission], strict: true);
    }
}
