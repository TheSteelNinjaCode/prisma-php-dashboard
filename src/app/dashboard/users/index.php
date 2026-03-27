<?php

use Lib\PPIcons\{Pencil, Trash};

use Lib\PHPXUI\{Button, Input, Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow};
use Lib\Prisma\Classes\Prisma;
use PP\ImportComponent;

$prisma = Prisma::getInstance();

$users = $prisma->user->findMany([
    'omit' => ['password' => true]
]);

?>

<div class="flex justify-between items-center mb-6">
    <Input placeholder="Search users..." class="w-fit" />
    <Button onclick="addNewUser()">
        Add User
    </Button>
</div>

<Table>
    <TableCaption>A list of your recent invoices.</TableCaption>
    <TableHeader>
        <TableRow>
            <TableHead>Name</TableHead>
            <TableHead>Email</TableHead>
            <TableHead class="text-right"></TableHead>
        </TableRow>
    </TableHeader>
    <TableBody>
        <template pp-for="user in users">
            <TableRow>
                <TableCell class="font-medium">{user.name}</TableCell>
                <TableCell>{user.email}</TableCell>
                <TableCell class="text-right">
                    <Button variant="outline" size="sm" onclick="editUser(user)">
                        <Pencil class="size-4" />
                    </Button>
                    <Button variant="destructive" size="sm">
                        <Trash class="size-4" />
                    </Button>
                </TableCell>
            </TableRow>
        </template>
    </TableBody>
</Table>

<?php ImportComponent::render(
    APP_PATH . '/components/dashboard/users/CreateUpdateDialog.php',
    [
        'openCreateUpdateDialog' => '{openCreateUpdateDialog}',
        'setOpenCreateUpdateDialog' => '{setOpenCreateUpdateDialog}',
        'selectedUser' => '{selectedUser}',
        'setSelectedUser' => '{setSelectedUser}',
        'users' => '{users}',
        'setUsers' => '{setUsers}',
    ]
); ?>

<script>
    const usersJson = <?= json_encode($users) ?>;
    console.log(usersJson);
    const [users, setUsers] = pp.state(usersJson);
    const [openCreateUpdateDialog, setOpenCreateUpdateDialog] = pp.state(false);
    const [selectedUser, setSelectedUser] = pp.state(null);

    function editUser(user) {
        setSelectedUser(user);
        setOpenCreateUpdateDialog(true);
    }

    function addNewUser() {
        setSelectedUser(null);
        setOpenCreateUpdateDialog(true);
    }
</script>