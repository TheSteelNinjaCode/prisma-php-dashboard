<?php

use Lib\PHPXUI\Button;
use Lib\PHPXUI\{Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, Toast};
use Lib\PHPXUI\Input;
use Lib\PHPXUI\Label;
use PP\Attributes\Exposed;
use PP\Validator;
use Lib\Prisma\Classes\Prisma;
use Lib\Auth\Auth;

$user = Auth::getInstance()->getPayload();

#[Exposed(requiresAuth: true)]
function updateProfile($data)
{
    $name = Validator::string($data->name ?? '');
    $email = Validator::email($data->email ?? '');
    $password = Validator::string($data->password ?? '');

    if (!$name && !$email) {
        return [
            'success' => false,
            'message' => 'Name or email is required'
        ];
    }

    $data = [
        'name' => $name,
        'email' => $email,
    ];

    if ($password) {
        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    $prisma = Prisma::getInstance();
    $userId = Auth::getInstance()->getPayload()->id;

    $prisma->user->update([
        'where' => [
            'id' => $userId
        ],
        'data' => $data,
    ]);

    return [
        'success' => true,
    ];
}

?>

<div>
    <Dialog open="{openProfileDialog}" onOpenChange="{setOpenProfileDialog}" closeOnOverlayClick="false">
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
                        <Input id="name" name="name" value="<?= $user->name ?>" />
                    </div>

                    <div class="grid gap-3">
                        <Label for="email">Email</Label>
                        <Input id="email" name="email" value="<?= $user->email ?>" />
                    </div>
                    <div class="grid gap-3">
                        <Label for="password">Password</Label>
                        <Input id="password" name="password" type="password" placeholder="Leave blank to use the current password" autocomplete="new-password" />
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

    <Toast open="{toast.show}" onOpenChange="{setToast}" description="{toast.description}" />

    <script>
        const [requestMessage, setRequestMessage] = pp.state('');
        const [toast, setToast] = pp.state({
            show: false,
            description: '',
        });

        async function handleSubmit(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            const response = await pp.fetchFunction('updateProfile', data);
            if (response.success) {
                setToast({
                    show: true,
                    description: 'Profile updated successfully',
                });
                setOpenProfileDialog(false);
                setRequestMessage('');
            } else {
                setRequestMessage(response.message);
            }
        }
    </script>
</div>