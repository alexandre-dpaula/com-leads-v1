<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if ($this->currentUser()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/login', [
            'old' => Session::getFlash('old', []),
            'errors' => Session::getFlash('errors', []),
            'flash' => Session::getFlash('flash'),
        ]);
    }

    public function login(): void
    {
        Csrf::assertValidFromRequest();

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $validator = new Validator();
        $validator->require('email', $email, 'Informe seu e-mail.')
            ->email('email', $email, 'E-mail inválido.')
            ->require('password', $password, 'Informe sua senha.');

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', ['email' => $email]);
            $this->redirect('/login');
        }

        $user = User::findByEmail($email);
        $delay = $this->rateLimitDelay($email);
        if ($delay > 0) {
            usleep($delay * 1_000_000);
        }

        if (!$user || !password_verify($password, $user->password_hash)) {
            $this->incrementRateLimit($email);
            Session::flash('flash', ['type' => 'error', 'message' => 'Credenciais inválidas.']);
            Session::flash('old', ['email' => $email]);
            $this->redirect('/login');
        }

        $this->resetRateLimit($email);
        Session::regenerate();
        Session::set('user_id', $user->id);
        $this->redirect('/dashboard');
    }

    public function showRegister(): void
    {
        if ($this->currentUser()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/register', [
            'old' => Session::getFlash('old', []),
            'errors' => Session::getFlash('errors', []),
            'flash' => Session::getFlash('flash'),
        ]);
    }

    public function register(): void
    {
        Csrf::assertValidFromRequest();

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        $validator = new Validator();
        $validator->require('name', $name, 'Informe seu nome.')
            ->require('email', $email, 'Informe seu e-mail.')
            ->email('email', $email, 'E-mail inválido.')
            ->unique(fn(string $value) => User::emailExists($value), 'email', $email, 'Este e-mail já está em uso.')
            ->require('password', $password, 'Informe uma senha.')
            ->minLength('password', $password, 8, 'A senha deve ter ao menos 8 caracteres.');

        if ($password !== $passwordConfirmation) {
            $errors = $validator->errors();
            $errors['password_confirmation'][] = 'As senhas não coincidem.';
            Session::flash('errors', $errors);
            Session::flash('old', ['name' => $name, 'email' => $email]);
            $this->redirect('/register');
        }

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', ['name' => $name, 'email' => $email]);
            $this->redirect('/register');
        }

        $user = User::create($name, $email, $password);

        Session::regenerate();
        Session::set('user_id', $user->id);
        Session::flash('flash', ['type' => 'success', 'message' => 'Conta criada com sucesso!']);
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Csrf::assertValidFromRequest();
        Session::destroy();
        $this->redirect('/');
    }

    private function incrementRateLimit(string $email): void
    {
        $attempts = Session::get('login_attempts', []);
        $attempts[$email] = ($attempts[$email] ?? 0) + 1;
        Session::set('login_attempts', $attempts);
    }

    private function resetRateLimit(string $email): void
    {
        $attempts = Session::get('login_attempts', []);
        unset($attempts[$email]);
        Session::set('login_attempts', $attempts);
    }

    private function rateLimitDelay(string $email): int
    {
        $attempts = Session::get('login_attempts', []);
        $count = $attempts[$email] ?? 0;
        return min($count, 5); // delay incremental até 5 segundos
    }
}

