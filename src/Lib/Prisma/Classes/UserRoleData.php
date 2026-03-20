<?php

declare(strict_types=1);

namespace Lib\Prisma\Classes;


class UserRoleData
{

    public ?UserRoleData $_avg = null;
    public ?UserRoleData $_count = null;
    public ?UserRoleData $_max = null;
    public ?UserRoleData $_min = null;
    public ?UserRoleData $_sum = null;
    public ?int $id;
    public string $name;
    /** @var UserData[] */
    public ?array $user;

    public function __construct(
        string $name,
        ?int $id = null,
        ?array $user = [],
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->user = $user;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user' => $this->user
        ];
    }
}