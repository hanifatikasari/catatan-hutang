<?php

namespace App\Controllers;

class Auth extends BaseController
{
    private const SESSION_KEY = 'auth_user';
    private const ATTEMPTS_KEY = 'auth_login_attempts';
    private const LOCKED_UNTIL_KEY = 'auth_locked_until';
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 900;

    public function login()
    {
        $this->setPrivateHeaders();

        if (is_array(session()->get(self::SESSION_KEY))) {
            return redirect()->to(site_url('/'));
        }

        return view('auth/login', [
            'error' => session()->getFlashdata('error'),
            'lockedUntil' => (int) session()->get(self::LOCKED_UNTIL_KEY),
        ]);
    }

    public function authenticate()
    {
        $this->setPrivateHeaders();

        $now = time();
        $lockedUntil = (int) session()->get(self::LOCKED_UNTIL_KEY);
        if ($lockedUntil > $now) {
            return redirect()->to(site_url('login'))->with('error', 'Terlalu banyak percobaan. Coba lagi beberapa menit nanti.');
        }

        $config = config('Auth');
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        $usernameValid = hash_equals($config->username, $username);
        $passwordValid = password_verify($password, $config->passwordHash);

        if (! $usernameValid || ! $passwordValid) {
            $attempts = (int) session()->get(self::ATTEMPTS_KEY) + 1;
            if ($attempts >= self::MAX_ATTEMPTS) {
                session()->set(self::LOCKED_UNTIL_KEY, $now + self::LOCK_SECONDS);
                session()->remove(self::ATTEMPTS_KEY);
                return redirect()->to(site_url('login'))->with('error', 'Login gagal 5 kali. Akses login dikunci sementara selama 15 menit.');
            }

            session()->set(self::ATTEMPTS_KEY, $attempts);
            return redirect()->to(site_url('login'))->with('error', 'Username atau password tidak sesuai.');
        }

        session()->remove([self::ATTEMPTS_KEY, self::LOCKED_UNTIL_KEY]);
        session()->regenerate(true);
        session()->set(self::SESSION_KEY, [
            'username' => $expectedUsername,
            'logged_in_at' => $now,
        ]);

        return redirect()->to(site_url('/'));
    }

    public function logout()
    {
        $this->setPrivateHeaders();
        session()->remove([self::SESSION_KEY, 'backup_restore_authorized_at']);
        session()->regenerate(true);

        return redirect()->to(site_url('login'))->with('success', 'Kamu berhasil keluar dari Catatan Hutang.');
    }

    private function setPrivateHeaders(): void
    {
        service('response')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0');
    }
}
