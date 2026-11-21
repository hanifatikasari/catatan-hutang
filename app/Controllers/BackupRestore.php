<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;
use Throwable;

class BackupRestore extends BaseController
{
    private const ACCESS_SESSION_KEY = 'backup_restore_authorized_at';
    private const ACCESS_TTL = 900; // 15 minutes
    private const MAX_UPLOAD_BYTES = 20 * 1024 * 1024;

    public function index()
    {
        return view('backup_restore/index', [
            'judul' => 'Backup & Restore Database',
            'authorized' => $this->hasAccess(),
            'passwordConfigured' => $this->configuredPassword() !== '',
            'error' => session()->getFlashdata('error'),
            'success' => session()->getFlashdata('success'),
        ]);
    }

    public function unlock()
    {
        $expected = $this->configuredPassword();
        $provided = (string) $this->request->getPost('password');

        if ($expected === '' || strlen($expected) < 16) {
            return redirect()->to('/backup-restore')->with('error', 'Password backup belum dikonfigurasi dengan aman. Isi BACKUP_RESTORE_PASSWORD di file .env dengan minimal 16 karakter.');
        }

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            return redirect()->to('/backup-restore')->with('error', 'Password tidak sesuai.');
        }

        session()->regenerate();
        session()->set(self::ACCESS_SESSION_KEY, time());

        return redirect()->to('/backup-restore')->with('success', 'Akses backup dan restore dibuka selama 15 menit.');
    }

    public function lock()
    {
        session()->remove(self::ACCESS_SESSION_KEY);
        session()->regenerate();

        return redirect()->to('/backup-restore')->with('success', 'Akses backup dan restore telah dikunci.');
    }

    public function download()
    {
        if (! $this->hasAccess()) {
            return redirect()->to('/backup-restore')->with('error', 'Silakan buka akses terlebih dahulu.');
        }

        try {
            $sql = $this->createSqlBackup();

            if (! is_string($sql) || $sql === '') {
                throw new RuntimeException('Database tidak menghasilkan file backup.');
            }

            $filename = 'catatan-hutang-backup-' . date('Ymd-His') . '.sql';

            return $this->response
                ->setHeader('Content-Type', 'application/sql; charset=UTF-8')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setHeader('X-Content-Type-Options', 'nosniff')
                ->setBody($sql);
        } catch (Throwable $e) {
            log_message('error', 'Backup database gagal: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('/backup-restore')->with('error', 'Backup gagal dibuat. Periksa log aplikasi.');
        }
    }

    public function restore()
    {
        if (! $this->hasAccess()) {
            return redirect()->to('/backup-restore')->with('error', 'Silakan buka akses terlebih dahulu.');
        }

        $upload = $this->request->getFile('backup_file');
        if (! $upload instanceof UploadedFile || ! $upload->isValid()) {
            return redirect()->to('/backup-restore')->with('error', 'Pilih file backup SQL yang valid.');
        }

        if (strtolower($upload->getClientExtension()) !== 'sql' || $upload->getSize() > self::MAX_UPLOAD_BYTES) {
            return redirect()->to('/backup-restore')->with('error', 'File harus berformat .sql dan maksimal 20 MB.');
        }

        $sql = file_get_contents($upload->getTempName());
        if (! is_string($sql) || trim($sql) === '' || ! preg_match('/\b(CREATE\s+TABLE|DROP\s+TABLE)\b/i', $sql)) {
            return redirect()->to('/backup-restore')->with('error', 'File tidak terlihat seperti backup database SQL.');
        }

        try {
            $allowedTables = db_connect()->listTables();
            $statements = $this->validatedBackupStatements($sql, $allowedTables);
            $currentBackup = $this->createSqlBackup();

            if (! is_string($currentBackup) || $currentBackup === '') {
                throw new RuntimeException('Tidak dapat membuat backup pengaman sebelum restore.');
            }

            $this->saveSafetyBackup($currentBackup);

            try {
                $this->executeBackupStatements($statements);
            } catch (Throwable $restoreError) {
                try {
                    $rollbackStatements = $this->validatedBackupStatements($currentBackup, $allowedTables);
                    $this->executeBackupStatements($rollbackStatements);
                } catch (Throwable $rollbackError) {
                    log_message('critical', 'Restore gagal dan backup pengaman otomatis juga gagal: {message}', ['message' => $rollbackError->getMessage()]);
                }

                throw $restoreError;
            }

            return redirect()->to('/backup-restore')->with('success', 'Database berhasil dipulihkan. Backup kondisi sebelum restore disimpan di writable/backups/');
        } catch (Throwable $e) {
            log_message('error', 'Restore database gagal: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('/backup-restore')->with('error', 'Restore gagal. Sistem mencoba mengembalikan backup pengaman; periksa log dan data sebelum melanjutkan.');
        }
    }

    /**
     * Build a plain SQL dump using MySQL-compatible metadata queries.
     * CI4's native Database Utils backup is not implemented for MySQLi.
     */
    private function createSqlBackup(): string
    {
        $db = db_connect();
        $tables = $db->listTables();

        if ($tables === []) {
            throw new RuntimeException('Database tidak memiliki tabel untuk dibackup.');
        }

        $lines = [
            '-- Catatan Hutang SQL backup',
            '-- Created: ' . date('Y-m-d H:i:s'),
            'SET FOREIGN_KEY_CHECKS = 0;',
            '',
        ];

        foreach ($tables as $table) {
            $quotedTable = $db->escapeIdentifiers($table);
            $createQuery = $db->query('SHOW CREATE TABLE ' . $quotedTable);
            $createRow = $createQuery->getRowArray();

            if (! is_array($createRow) || count($createRow) < 2) {
                throw new RuntimeException('Tidak dapat membaca struktur tabel: ' . $table);
            }

            $createSql = (string) array_values($createRow)[1];
            if ($createSql === '') {
                throw new RuntimeException('Struktur tabel kosong: ' . $table);
            }

            $lines[] = 'DROP TABLE IF EXISTS ' . $quotedTable . ';';
            $lines[] = $createSql . ';';
            $lines[] = '';

            $dataQuery = $db->query('SELECT * FROM ' . $quotedTable);
            $columns = $dataQuery->getFieldNames();
            $rows = $dataQuery->getResultArray();

            if ($columns === []) {
                continue;
            }

            $quotedColumns = array_map(
                static fn (string $column): string => $db->escapeIdentifiers($column),
                $columns
            );
            $columnSql = implode(', ', $quotedColumns);

            foreach ($rows as $row) {
                $values = [];
                foreach ($columns as $column) {
                    $values[] = $db->escape($row[$column] ?? null);
                }

                $lines[] = 'INSERT INTO ' . $quotedTable
                    . ' (' . $columnSql . ') VALUES (' . implode(', ', $values) . ');';
            }

            $lines[] = '';
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS = 1;';
        return implode("\n", $lines) . "\n";
    }

    private function configuredPassword(): string
    {
        $value = env('BACKUP_RESTORE_PASSWORD');
        return is_string($value) ? trim($value) : '';
    }

    private function hasAccess(): bool
    {
        $timestamp = session()->get(self::ACCESS_SESSION_KEY);
        if (! is_int($timestamp) || time() - $timestamp > self::ACCESS_TTL) {
            session()->remove(self::ACCESS_SESSION_KEY);
            return false;
        }

        return true;
    }

    /**
     * Accept only the DROP / CREATE / INSERT statements produced by CI4's
     * MySQL database utility and only for tables already present in this DB.
     *
     * @return list<string>
     */
    private function validatedBackupStatements(string $sql, array $existingTables): array
    {
        $statements = $this->splitSqlStatements($sql);
        $validated = [];
        $hasCreate = false;
        $hasDrop = false;

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }

            if (preg_match('/^DROP\s+TABLE\s+IF\s+EXISTS\s+`?([a-zA-Z0-9_]+)`?$/i', $statement, $match)) {
                $table = $match[1];
                if (! in_array($table, $existingTables, true)) {
                    throw new RuntimeException('Backup memuat tabel yang tidak dikenal.');
                }
                $hasDrop = true;
                $validated[] = $statement;
                continue;
            }

            if (preg_match('/^CREATE\s+TABLE\s+`?([a-zA-Z0-9_]+)`?\s*\(/i', $statement, $match)) {
                $table = $match[1];
                if (! in_array($table, $existingTables, true)) {
                    throw new RuntimeException('Backup memuat tabel yang tidak dikenal.');
                }
                $hasCreate = true;
                $validated[] = $statement;
                continue;
            }

            if (preg_match('/^INSERT\s+INTO\s+`?([a-zA-Z0-9_]+)`?\s*(?:\([^)]*\))?\s+VALUES\s*\(/i', $statement, $match)) {
                $table = $match[1];
                if (! in_array($table, $existingTables, true)) {
                    throw new RuntimeException('Backup memuat tabel yang tidak dikenal.');
                }
                $validated[] = $statement;
                continue;
            }

            // Ignore comments and the FK-check toggles that may be in a dump.
            if (preg_match('/^SET\s+(?:SESSION\s+)?FOREIGN_KEY_CHECKS\s*=\s*[01]$/i', $statement)) {
                continue;
            }

            throw new RuntimeException('Backup berisi perintah SQL yang tidak diizinkan.');
        }

        if (! $hasCreate || ! $hasDrop || $validated === []) {
            throw new RuntimeException('Backup tidak lengkap atau bukan hasil ekspor database yang didukung.');
        }

        return $validated;
    }

    /**
     * Split SQL on semicolons outside quoted strings and comments.
     *
     * @return list<string>
     */
    private function splitSqlStatements(string $sql): array
    {
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql) ?? $sql;
        $statements = [];
        $buffer = '';
        $quote = null;
        $length = strlen($sql);
        $lineComment = false;
        $blockComment = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($lineComment) {
                if ($char === "\n") {
                    $lineComment = false;
                    $buffer .= "\n";
                }
                continue;
            }

            if ($blockComment) {
                if ($char === '*' && $next === '/') {
                    $blockComment = false;
                    $i++;
                }
                continue;
            }

            if ($quote !== null) {
                $buffer .= $char;
                if ($char === '\\' && $quote !== '`' && $next !== '') {
                    $buffer .= $next;
                    $i++;
                    continue;
                }
                if ($char === $quote) {
                    if ($next === $quote) {
                        $buffer .= $next;
                        $i++;
                    } else {
                        $quote = null;
                    }
                }
                continue;
            }

            if ($char === '-' && $next === '-' && ($i + 2 >= $length || ctype_space($sql[$i + 2]))) {
                $lineComment = true;
                $i++;
                continue;
            }
            if ($char === '#') {
                $lineComment = true;
                continue;
            }
            if ($char === '/' && $next === '*') {
                $blockComment = true;
                $i++;
                continue;
            }
            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $buffer .= $char;
                continue;
            }
            if ($char === ';') {
                if (trim($buffer) !== '') {
                    $statements[] = trim($buffer);
                }
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            $statements[] = trim($buffer);
        }

        return $statements;
    }

    /**
     * @param list<string> $statements
     */
    private function executeBackupStatements(array $statements): void
    {
        $db = db_connect();
        $db->query('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($statements as $statement) {
                $result = $db->query($statement);
                if ($result === false) {
                    throw new RuntimeException('Salah satu perintah SQL gagal dijalankan.');
                }
            }
        } finally {
            $db->query('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    private function saveSafetyBackup(string $sql): void
    {
        $directory = WRITEPATH . 'backups';
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Tidak dapat membuat folder backup pengaman.');
        }

        $backupPath = $directory . DIRECTORY_SEPARATOR . 'before_restore_latest.sql';
        if (file_put_contents($backupPath, $sql, LOCK_EX) === false) {
            throw new RuntimeException('Tidak dapat menyimpan backup pengaman.');
        }
        @chmod($backupPath, 0600);
        file_put_contents($directory . DIRECTORY_SEPARATOR . 'index.html', '', LOCK_EX);
        file_put_contents($directory . DIRECTORY_SEPARATOR . '.htaccess', "Require all denied\nDeny from all\n", LOCK_EX);
    }
}
