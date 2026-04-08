<?php

use Lib\PPIcons\{Pencil, Trash, Search, Loader, Plus};
use Lib\PHPXUI\{
    Button,
    Input,
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
};
use Lib\Prisma\Classes\Prisma;
use PP\ImportComponent;
use PP\Attributes\Exposed;

$prisma = Prisma::getInstance();

#[Exposed]
function fetchUsers($data)
{
    $prisma = Prisma::getInstance();

    $search = trim((string)($data->search ?? ''));
    $page = max(1, (int)($data->page ?? 1));
    $pageSize = 10;
    $skip = ($page - 1) * $pageSize;

    $where = [];

    if ($search !== '') {
        $where['OR'] = [
            ['name' => ['contains' => $search]],
            ['email' => ['contains' => $search]],
        ];
    }

    $totalUsers = (int) $prisma->user->count([
        'where' => $where
    ]);

    $users = $prisma->user->findMany([
        'where' => $where,
        'omit' => ['password' => true],
        'orderBy' => [
            'createdAt' => 'desc'
        ],
        'take' => $pageSize,
        'skip' => $skip,
    ]);

    return [
        'users' => $users,
        'pagination' => [
            'currentPage' => $page,
            'pageSize' => $pageSize,
            'totalUsers' => $totalUsers,
            'hasMore' => ($skip + $pageSize) < $totalUsers,
            'totalPages' => $pageSize > 0 ? (int) ceil($totalUsers / $pageSize) : 1,
        ],
    ];
}

$initialData = fetchUsers((object) [
    'search' => '',
    'page' => 1,
]);

?>

<div class="flex justify-between items-center gap-4 mb-6">
    <div class="relative w-full max-w-sm">
        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none">
            <Search class="size-4" />
        </div>

        <Input
            type="text"
            placeholder="Search users..."
            class="pl-9 pr-9"
            oninput="handleSearch(event)" />

        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-primary pointer-events-none {isLoading ? 'opacity-100' : 'opacity-0'}">
            <Loader class="size-4 animate-spin" />
        </div>
    </div>

    <Button onclick="addNewUser()">
        <Plus class="size-4" />
        <span class="ml-2">Add User</span>
    </Button>
</div>

<div class="rounded-sm border border-border bg-card overflow-hidden">
    <Table>
        <TableCaption>
            <span>Total users: {pagination.totalUsers}</span>
        </TableCaption>

        <TableHeader>
            <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Email</TableHead>
                <TableHead class="text-right">Actions</TableHead>
            </TableRow>
        </TableHeader>

        <TableBody>
            <template pp-for="user in users">
                <TableRow>
                    <TableCell class="font-medium">{user.name}</TableCell>
                    <TableCell>{user.email}</TableCell>
                    <TableCell class="text-right">
                        <div class="flex justify-end gap-2">
                            <Button variant="outline" size="sm" onclick="editUser(user)">
                                <Pencil class="size-4" />
                            </Button>

                            <Button variant="destructive" size="sm" onclick="deleteUser(user)">
                                <Trash class="size-4" />
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>
            </template>

            <TableRow hidden="{users.length !== 0 || isLoading}">
                <TableCell colspan="3" class="text-center py-10 text-muted-foreground">
                    No users found.
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>

    <div class="flex items-center justify-between gap-4 border-t border-border px-4 py-4">
        <div class="text-sm text-muted-foreground">
            Page <span class="font-medium text-foreground">{pagination.currentPage}</span>
            of
            <span class="font-medium text-foreground">{pagination.totalPages}</span>
        </div>

        <div class="flex items-center gap-2">
            <Button
                variant="outline"
                size="sm"
                onclick="goToPreviousPage()"
                disabled="{pagination.currentPage <= 1 || isLoading}">
                Previous
            </Button>

            <Button
                variant="outline"
                size="sm"
                onclick="goToNextPage()"
                disabled="{!pagination.hasMore || isLoading}">
                <Loader class="size-4 animate-spin mr-2 {isLoading ? 'block' : 'hidden'}" />
                <span>Next</span>
            </Button>
        </div>
    </div>
</div>

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
<?php ImportComponent::render(
    APP_PATH . '/components/dashboard/users/DeleteDialog.php',
    [
        'openDeleteDialog' => '{openDeleteDialog}',
        'setOpenDeleteDialog' => '{setOpenDeleteDialog}',
        'selectedUser' => '{selectedUser}',
        'setSelectedUser' => '{setSelectedUser}',
        'users' => '{users}',
        'setUsers' => '{setUsers}',
    ]
); ?>

<script>
    const initialUsers = <?= json_encode($initialData['users']) ?>;
    const initialPagination = <?= json_encode($initialData['pagination']) ?>;

    const [users, setUsers] = pp.state(initialUsers);
    const [pagination, setPagination] = pp.state(initialPagination);
    const [openCreateUpdateDialog, setOpenCreateUpdateDialog] = pp.state(false);
    const [openDeleteDialog, setOpenDeleteDialog] = pp.state(false);
    const [selectedUser, setSelectedUser] = pp.state(null);
    const [isLoading, setIsLoading] = pp.state(false);
    const [searchQuery, setSearchQuery] = pp.state('');

    let searchTimeout = null;

    function editUser(user) {
        setSelectedUser(user);
        setOpenCreateUpdateDialog(true);
    }

    function deleteUser(user) {
        setSelectedUser(user);
        setOpenDeleteDialog(true);
    }

    function addNewUser() {
        setSelectedUser(null);
        setOpenCreateUpdateDialog(true);
    }

    async function loadUsers(targetPage = 1, search = searchQuery) {
        setIsLoading(true);

        try {
            const response = await pp.fetchFunction('fetchUsers', {
                page: targetPage,
                search: search
            });

            if (response) {
                setUsers(response.users || []);
                setPagination(response.pagination || {
                    currentPage: 1,
                    pageSize: 10,
                    totalUsers: 0,
                    hasMore: false,
                    totalPages: 1
                });
            }
        } catch (error) {
            console.error('Error loading users:', error);
        } finally {
            setIsLoading(false);
        }
    }

    function handleSearch(event) {
        const value = event.target.value || '';
        setSearchQuery(value);

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        searchTimeout = setTimeout(() => {
            loadUsers(1, value);
        }, 350);
    }

    function goToNextPage() {
        if (isLoading || !pagination.hasMore) {
            return;
        }

        loadUsers((pagination.currentPage || 1) + 1, searchQuery);
    }

    function goToPreviousPage() {
        if (isLoading || (pagination.currentPage || 1) <= 1) {
            return;
        }

        loadUsers((pagination.currentPage || 1) - 1, searchQuery);
    }
</script>