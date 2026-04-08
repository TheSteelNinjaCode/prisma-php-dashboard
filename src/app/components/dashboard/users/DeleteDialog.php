<?php

use Lib\Auth\Auth;
use Lib\PHPXUI\{AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger, Toast};
use Lib\PHPXUI\Button;
use Lib\Prisma\Classes\Prisma;
use PP\Attributes\Exposed;
use PP\Validator;

#[Exposed(requiresAuth: true)]
function deleteUser($data)
{
    $userId = Validator::cuid($data->id ?? null);

    if (!$userId) {
        return [
            'success' => false,
            'message' => 'Invalid user ID',
        ];
    }

    $user = Auth::getInstance()->getPayload();

    if ($userId === $user->id) {
        return [
            'success' => false,
            'message' => 'You cannot delete your own account',
        ];
    }

    $prisma = Prisma::getInstance();
    $prisma->user->delete([
        'where' => [
            'id' => $userId
        ],
    ]);

    return [
        'success' => true,
        'message' => 'User deleted successfully',
    ];
}

?>

<div>
    <AlertDialog open="{openDeleteDialog}" onOpenChange="{setOpenDeleteDialog}">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This action cannot be undone. This will permanently delete your
                    account and remove your data from our servers.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction onclick="deleteUser()">Continue</AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>

    <Toast open="{toast.show}" onOpenChange="{setToast}" description="{toast.description}" />

    <script>
        const [toast, setToast] = pp.state({
            show: false,
            description: '',
        });

        async function deleteUser() {
            console.log('Deleting user', selectedUser);
            const response = await pp.fetchFunction('deleteUser', {
                id: selectedUser.id
            });
            if (response.success) {
                setUsers(users.filter(u => u.id !== selectedUser.id));
                setToast({
                    show: true,
                    description: response.message || 'User deleted successfully',
                });
                setOpenDeleteDialog(false);
            } else {
                setToast({
                    show: true,
                    description: response.message || 'Failed to delete user',
                });
            }
        }
    </script>
</div>