<?php

declare(strict_types=1);

namespace Lib\Prisma\Classes;

use DateTime;
use DateTimeInterface;

class UserData
{

    public ?UserData $_avg = null;
    public ?UserData $_count = null;
    public ?UserData $_max = null;
    public ?UserData $_min = null;
    public ?UserData $_sum = null;
    public ?string $id;
    public ?string $name;
    public ?string $email;
    public ?string $password;
    public DateTime|string|null $emailVerified;
    public ?string $image;
    public DateTime|string $createdAt;
    public DateTime|string $updatedAt;
    public ?int $roleId;
    public ?UserRoleData $userRole;

    public function __construct(
        ?string $id = null,
        ?string $name = null,
        ?string $email = null,
        ?string $password = null,
        DateTime|string|null $emailVerified = null,
        ?string $image = null,
        DateTime|string $createdAt = new DateTime(),
        DateTime|string $updatedAt = new DateTime(),
        ?int $roleId = null,
        ?UserRoleData $userRole = null,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->emailVerified = $emailVerified;
        $this->image = $image;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->roleId = $roleId;
        $this->userRole = $userRole;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'emailVerified' => ($this->emailVerified instanceof DateTimeInterface) ? $this->emailVerified->format('Y-m-d H:i:s') : ($this->emailVerified !== null && $this->emailVerified !== '' ? (string)$this->emailVerified : null),
            'image' => $this->image,
            'createdAt' => ($this->createdAt instanceof DateTimeInterface) ? $this->createdAt->format('Y-m-d H:i:s') : ($this->createdAt !== null && $this->createdAt !== '' ? (string)$this->createdAt : null),
            'updatedAt' => ($this->updatedAt instanceof DateTimeInterface) ? $this->updatedAt->format('Y-m-d H:i:s') : ($this->updatedAt !== null && $this->updatedAt !== '' ? (string)$this->updatedAt : null),
            'roleId' => $this->roleId,
            'userRole' => $this->userRole
        ];
    }
}