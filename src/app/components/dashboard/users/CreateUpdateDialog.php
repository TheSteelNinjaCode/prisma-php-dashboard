<?php

use Lib\PHPXUI\{Button, Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, Input, Label};
use PP\Attributes\Exposed;
use PP\Validator;
use Lib\Prisma\Classes\Prisma;

#[Exposed(requiresAuth: true)]
function createUpdateUser($data)
{
    $id = Validator::cuid($data->id ?? '');
    $name = Validator::string($data->name ?? '');
    $email = Validator::email($data->email ?? '');
    $password = Validator::string($data->password ?? '');

    if (!$name || !$email) {
        return [
            'success' => false,
            'message' => 'Name and email are required.',
        ];
    }

    if (!$id && !$password) {
        return [
            'success' => false,
            'message' => 'Password is required for new users.',
        ];
    }

    $prisma = Prisma::getInstance();

    $emailExists = $prisma->user->findUnique([
        'where' => [
            'email' => $email,
        ],
    ]);

    if ($emailExists && $emailExists->id !== $id) {
        return [
            'success' => false,
            'message' => 'Email already exists.',
        ];
    }

    if ($id) {
        $hasPassword = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
        $data = [
            'name' => $name,
            'email' => $email,
        ];

        if ($hasPassword) {
            $data['password'] = $hasPassword;
        }

        $updatedUser = $prisma->user->update([
            'where' => ['id' => $id],
            'data' => $data,
        ]);

        return [
            'success' => true,
            'message' => 'User updated successfully.',
            'user' => $updatedUser,
        ];
    } else {
        $hasPassword = password_hash($password, PASSWORD_DEFAULT);

        $newUser = $prisma->user->create([
            'data' => [
                'name' => $name,
                'email' => $email,
                'password' => $hasPassword,
            ],
        ]);

        return [
            'success' => true,
            'message' => 'User created successfully.',
            'user' => $newUser,
        ];
    }
}

?>

<div>
    <Dialog open="{openCreateUpdateDialog}" onOpenChange="{setOpenCreateUpdateDialog}" closeOnOverlayClick="false">
        <form onsubmit="handleSubmit(event)">
            <DialogContent class="sm:max-w-106.25">
                <DialogHeader>
                    <DialogTitle>Edit profile</DialogTitle>
                    <DialogDescription>
                        Make changes to your profile here. Click save when you're done.
                    </DialogDescription>
                    <p class="text-destructive">{requestMessage}</p>
                </DialogHeader>

                <div class="grid gap-4">
                    <div class="grid gap-3">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" value="{selectedUser?.name}" required="true" />
                    </div>

                    <div class="grid gap-3">
                        <Label for="email">Email</Label>
                        <Input id="email" name="email" value="{selectedUser?.email}" required="true" />
                    </div>
                    <div class="grid gap-3">
                        <Label for="password">Password</Label>
                        <Input id="password" name="password" type="password" placeholder="Empty to keep current password" autocomplete="new-password" />
                    </div>
                </div>

                <DialogFooter>
                    <DialogClose asChild="true">
                        <Button variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button type="submit">Save changes</Button>
                </DialogFooter>
            </DialogContent>
        </form>
    </Dialog>

    <script>
        const [requestMessage, setRequestMessage] = pp.state('');

        async function handleSubmit(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            console.log("Form data:", data);

            if (selectedUser) {
                data.id = selectedUser.id;
            }

            if (selectedUser) {
                const response = await pp.fetchFunction('createUpdateUser', data);
                console.log("Response from createUpdateUser:", response);
                if (response.success) {
                    const updatedUsers = users.map(user => user.id === response.user.id ? response.user : user);
                    setUsers(updatedUsers);
                    setOpenCreateUpdateDialog(false);
                } else {
                    setRequestMessage('Failed to update user: ' + response.message);
                }
            } else {
                const response = await pp.fetchFunction('createUpdateUser', data);
                console.log("Response from createUpdateUser:", response);

                if (response.success) {
                    setUsers([...users, response.user]);
                    setOpenCreateUpdateDialog(false);
                } else {
                    setRequestMessage('Failed to create user: ' + response.message);
                }
            }
        }
    </script>
</div>