<?php

use Lib\Auth\Auth;
use Lib\PHPXUI\{Button, Input};
use PP\Attributes\Exposed;
use PP\Validator;
use Lib\Prisma\Classes\Prisma;

#[Exposed]
function signin($data)
{
    $email = Validator::email($data->email ?? '');
    $password = Validator::string($data->password ?? '');

    if (!$email || !$password) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    $prisma = Prisma::getInstance();

    $userExists = $prisma->user->findFirst([
        'where' => ['email' => $email],
        'include' => [
            'userRole' => true
        ]
    ]);

    if (!$userExists || !password_verify($password, $userExists->password)) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    $userData = [
        'id' => $userExists->id,
        'name' => $userExists->name,
        'email' => $userExists->email,
        'role' => $userExists->userRole
    ];

    Auth::getInstance()->signIn(data: $userData, redirect: true);
}

?>

<main class="grid place-items-center h-screen w-screen">
    <!-- Form Section -->
    <section class="bg-surface/40 backdrop-blur-sm flex flex-col justify-center px-8 sm:px-12 py-12 rounded-3xl border border-outline-variant/20 shadow-xl">
        <div class="max-w-md w-full mx-auto">
            <header class="mb-10 text-center">
                <h1 class="font-headline text-4xl font-bold text-on-background tracking-tight mb-3">Welcome</h1>
                <p class="font-body text-on-surface-variant text-base">Enter your credentials to access your enterprise dashboard.</p>
                <p class="text-destructive">{requestMessage}</p>
            </header>
            <!-- Main Login Form -->
            <form class="space-y-6" onsubmit="handleSubmit(event)">
                <div>
                    <label class="block font-label text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2 px-1" for="email">Email Address</label>
                    <div class="relative group">
                        <Input placeholder="name@company.com" type="email" name="email" required="true" />
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2 px-1">
                        <label class="block font-label text-xs font-bold text-on-surface-variant uppercase tracking-wider" for="password">Password</label>
                    </div>
                    <div class="relative group">
                        <Input placeholder="••••••••" type="password" name="password" required="true" />
                    </div>
                </div>
                <Button class="w-full" type="submit">
                    Sign In
                </Button>
            </form>
        </div>
    </section>
</main>

<script>
    const [requestMessage, setRequestMessage] = pp.state('');

    async function handleSubmit(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        const response = await pp.fetchFunction('signin', data);

        if (response.success) {
            setRequestMessage('');
        } else {
            setRequestMessage(response.message || 'Sign in failed. Please try again.');
        }
    }
</script>