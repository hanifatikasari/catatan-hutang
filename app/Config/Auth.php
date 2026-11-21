<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Auth extends BaseConfig
{
    /**
     * Default username for the single private account.
     * May be overridden in the server's ignored .env file.
     */
    public string $username = 'zulaikhah';

    /**
     * Keep the password hash in .env, not in the Git repository.
     * Generate it with PHP password_hash(PASSWORD_DEFAULT).
     */
    public string $passwordHash = '';
}
