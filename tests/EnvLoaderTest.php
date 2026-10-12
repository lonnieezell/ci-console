<?php

declare(strict_types=1);

use CodeIgniter\Console\EnvLoader;

describe('EnvLoader', function () {
    beforeEach(function () {
        // Clear $_ENV before each test
        foreach (array_keys($_ENV) as $key) {
            unset($_ENV[$key]);
        }
    });

    describe('load', function () {
        it('loads environment variables from .env file', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "APP_NAME=TestApp\nAPP_ENV=testing\nDEBUG=true\n");

            EnvLoader::load($tempDir);

            expect($_ENV['APP_NAME'])->toBe('TestApp');
            expect($_ENV['APP_ENV'])->toBe('testing');
            expect($_ENV['DEBUG'])->toBe('true');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('does not load when .env file does not exist', function () {
            $tempDir = sys_get_temp_dir().'/env_test_nonexistent_'.uniqid();

            EnvLoader::load($tempDir);

            expect($_ENV)->toBeEmpty();
        });

        it('skips empty lines', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "APP_NAME=TestApp\n\n\nAPP_ENV=testing\n");

            EnvLoader::load($tempDir);

            expect($_ENV)->toHaveKeys(['APP_NAME', 'APP_ENV']);
            expect($_ENV['APP_NAME'])->toBe('TestApp');
            expect($_ENV['APP_ENV'])->toBe('testing');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('skips comment lines', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "# This is a comment\nAPP_NAME=TestApp\n# Another comment\nAPP_ENV=testing\n");

            EnvLoader::load($tempDir);

            expect($_ENV)->toHaveKeys(['APP_NAME', 'APP_ENV']);
            expect($_ENV)->not()->toHaveKey('# This is a comment');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('skips lines without = separator', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "INVALID_LINE\nAPP_NAME=TestApp\nANOTHER_INVALID\n");

            EnvLoader::load($tempDir);

            expect($_ENV)->toHaveKeys(['APP_NAME']);
            expect($_ENV)->not()->toHaveKey('INVALID_LINE');
            expect($_ENV)->not()->toHaveKey('ANOTHER_INVALID');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('skips lines with empty keys', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "=value\nAPP_NAME=TestApp\n  =anothervalue\n");

            EnvLoader::load($tempDir);

            expect($_ENV)->toHaveKeys(['APP_NAME']);
            expect($_ENV['APP_NAME'])->toBe('TestApp');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('handles directory paths with trailing slashes', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "APP_NAME=TestApp\n");

            EnvLoader::load($tempDir.'/');

            expect($_ENV['APP_NAME'])->toBe('TestApp');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('handles multiple = separators in value', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "DATABASE_URL=mysql://user:pass=word@localhost\nAPI_KEY=key=value=here\n");

            EnvLoader::load($tempDir);

            expect($_ENV['DATABASE_URL'])->toBe('mysql://user:pass=word@localhost');
            expect($_ENV['API_KEY'])->toBe('key=value=here');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('unquotes double-quoted values', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "APP_NAME=\"My Test App\"\nDESCRIPTION=\"A long description with spaces\"\n");

            EnvLoader::load($tempDir);

            expect($_ENV['APP_NAME'])->toBe('My Test App');
            expect($_ENV['DESCRIPTION'])->toBe('A long description with spaces');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('unquotes single-quoted values', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "APP_NAME='My Test App'\nDESCRIPTION='A long description'\n");

            EnvLoader::load($tempDir);

            expect($_ENV['APP_NAME'])->toBe('My Test App');
            expect($_ENV['DESCRIPTION'])->toBe('A long description');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('handles unquoted values with spaces', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "APP_NAME=TestApp\nKEY=value  with  spaces\n");

            EnvLoader::load($tempDir);

            expect($_ENV['APP_NAME'])->toBe('TestApp');
            expect($_ENV['KEY'])->toBe('value  with  spaces');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('preserves empty quoted values', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "EMPTY_DOUBLE=\"\"\nEMPTY_SINGLE=''\nEMPTY_VALUE=\n");

            EnvLoader::load($tempDir);

            expect($_ENV['EMPTY_DOUBLE'])->toBe('');
            expect($_ENV['EMPTY_SINGLE'])->toBe('');
            expect($_ENV['EMPTY_VALUE'])->toBe('');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('handles values with quotes inside quotes', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "QUOTED=\"value with 'single' quotes\"\nSINGLE='value with \"double\" quotes'\n");

            EnvLoader::load($tempDir);

            expect($_ENV['QUOTED'])->toBe("value with 'single' quotes");
            expect($_ENV['SINGLE'])->toBe('value with "double" quotes');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('trims whitespace around keys and values', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "  APP_NAME  =  TestApp  \n\tKEY\t=\tvalue\t\n");

            EnvLoader::load($tempDir);

            expect($_ENV['APP_NAME'])->toBe('TestApp');
            expect($_ENV['KEY'])->toBe('value');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('handles numeric values', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "PORT=8080\nTIMEOUT=30.5\nCOUNT=-42\n");

            EnvLoader::load($tempDir);

            expect($_ENV['PORT'])->toBe('8080');
            expect($_ENV['TIMEOUT'])->toBe('30.5');
            expect($_ENV['COUNT'])->toBe('-42');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('handles boolean-like values', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "DEBUG=true\nPRODUCTION=false\nENABLED=yes\nDISABLED=no\n");

            EnvLoader::load($tempDir);

            expect($_ENV['DEBUG'])->toBe('true');
            expect($_ENV['PRODUCTION'])->toBe('false');
            expect($_ENV['ENABLED'])->toBe('yes');
            expect($_ENV['DISABLED'])->toBe('no');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('overwrites existing $_ENV values', function () {
            $_ENV['EXISTING_KEY'] = 'old_value';

            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "EXISTING_KEY=new_value\n");

            EnvLoader::load($tempDir);

            expect($_ENV['EXISTING_KEY'])->toBe('new_value');

            unlink($envFile);
            rmdir($tempDir);
        });

        it('handles URLs and connection strings', function () {
            $tempDir = sys_get_temp_dir().'/env_test_'.uniqid();
            mkdir($tempDir);
            $envFile = $tempDir.'/.env';
            file_put_contents($envFile, "DATABASE_URL=\"mysql://user:password@localhost:3306/dbname\"\nREDIS_URL=\"redis://localhost:6379\"\n");

            EnvLoader::load($tempDir);

            expect($_ENV['DATABASE_URL'])->toBe('mysql://user:password@localhost:3306/dbname');
            expect($_ENV['REDIS_URL'])->toBe('redis://localhost:6379');

            unlink($envFile);
            rmdir($tempDir);
        });
    });
});
