<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

final class ProfileController extends Controller
{
    public function edit(): void
    {
        $user = $this->requireAuth();

        $this->render('profile/edit', [
            'user' => $user,
            'errors' => Session::getFlash('errors', []),
            'flash' => Session::getFlash('flash'),
        ]);
    }

    public function update(): void
    {
        $user = $this->requireAuth();
        Csrf::assertValidFromRequest();

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        $validator = new Validator();
        $validator->require('name', $name, 'Informe seu nome.')
            ->require('email', $email, 'Informe seu e-mail.')
            ->email('email', $email, 'E-mail inválido.')
            ->unique(fn(string $value) => User::emailExists($value, $user->id), 'email', $email, 'Este e-mail já está em uso por outro usuário.');

        if ($password !== '') {
            $validator->minLength('password', $password, 8, 'A senha deve ter ao menos 8 caracteres.');
            if ($password !== $passwordConfirmation) {
                $errors = $validator->errors();
                $errors['password_confirmation'][] = 'As senhas não coincidem.';
                Session::flash('errors', $errors);
                $this->redirect('/profile');
            }
        }

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/profile');
        }

        User::updateProfile($user->id, $name, $email);

        if ($password !== '') {
            User::updatePassword($user->id, $password);
        }

        Session::flash('flash', ['type' => 'success', 'message' => 'Perfil atualizado com sucesso.']);
        $this->redirect('/profile');
    }
}

