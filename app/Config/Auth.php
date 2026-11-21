<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Auth extends BaseConfig
{
    /**
     * Single private account for the shop.
     * The password is stored as a one-way password_hash(), never as plaintext.
     */
    public string $username = 'zulaikhah';

    public string $passwordHash = '$2y$12$vfXlbFhaQRvtM3ozk60t/.ForahWOWW4OjGxdfRSfOQHFaB4MD9EO';
}
