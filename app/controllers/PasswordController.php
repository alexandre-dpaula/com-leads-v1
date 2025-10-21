<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Validator;
use App\Models\PasswordReset;
use App\Models\User;

final class PasswordController extends Controller
{
    public function showForgot(): void
    {
        if ($this->currentUser()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/forgot', [
            'errors' => Session::getFlash('errors', []),
            'flash' => Session::getFlash('flash'),
            'old' => Session::getFlash('old', []),
        ]);
    }

    public function sendResetLink(): void
    {
        Csrf::assertValidFromRequest();

        $email = trim((string) ($_POST['email'] ?? ''));
        $validator = new Validator();
        $validator->require('email', $email, 'Informe seu e-mail.')
            ->email('email', $email, 'E-mail inválido.');

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', ['email' => $email]);
            $this->redirect('/password/forgot');
        }

        $user = User::findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = new \DateTimeImmutable('+' . \PASSWORD_RESET_TOKEN_TTL . ' seconds');
            PasswordReset::create($user->id, $token, $expiresAt);

            // In a production environment the token should be sent via e-mail.
            Session::flash('flash', [
                'type' => 'success',
                'message' => 'Se o e-mail estiver cadastrado, enviaremos instruções para redefinir a senha.',
                'debug_token' => $token, // útil para ambiente de teste
            ]);
        } else {
            Session::flash('flash', [
                'type' => 'success',
                'message' => 'Se o e-mail estiver cadastrado, enviaremos instruções para redefinir a senha.',
            ]);
        }

        $this->redirect('/password/forgot');
    }

    public function showReset(): void
    {
        if ($this->currentUser()) {
            $this->redirect('/dashboard');
        }

        $token = $_GET['token'] ?? '';
        $this->render('auth/reset', [
            'token' => $token,
            'errors' => Session::getFlash('errors', []),
            'flash' => Session::getFlash('flash'),
        ]);
    }

    public function reset(): void
    {
        Csrf::assertValidFromRequest();

        $token = (string) ($_POST['token'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        $validator = new Validator();
        $validator->require('token', $token, 'Token inválido.')
            ->require('password', $password, 'Informe a nova senha.')
            ->minLength('password', $password, 8, 'A nova senha deve ter ao menos 8 caracteres.');

        if ($password !== $passwordConfirmation) {
            $errors = $validator->errors();
            $errors['password_confirmation'][] = 'As senhas não coincidem.';
            Session::flash('errors', $errors);
            $this->redirect('/password/reset?token=' . urlencode($token));
        }

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/password/reset?token=' . urlencode($token));
        }

        $reset = PasswordReset::findValid($token);
        if (!$reset) {
            Session::flash('flash', ['type' => 'error', 'message' => 'Token inválido ou expirado.']);
            $this->redirect('/password/forgot');
        }

        $user = User::findById((int) $reset['user_id']);
        if (!$user) {
            Session::flash('flash', ['type' => 'error', 'message' => 'Usuário não encontrado.']);
            $this->redirect('/password/forgot');
        }

        User::updatePassword($user->id, $password);
        PasswordReset::markUsed((int) $reset['id']);

        Session::flash('flash', ['type' => 'success', 'message' => 'Senha redefinida com sucesso. Faça login.']);
        $this->redirect('/login');
    }
}

