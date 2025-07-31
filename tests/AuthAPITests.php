<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// On déclare un namespace pour isoler les fonctions globales à mocker
namespace Tests\AuthAPITest {
    // Variables pour contrôler le mock
    $mockUserInfo = null;
    $mockGenerateJWT = null;

    // Mock de getUserInfo
    function getUserInfo($email) {
        global $mockUserInfo;
        return $mockUserInfo;
    }

    // Mock de generateJWT
    function generateJWT($payload) {
        global $mockGenerateJWT;
        if (is_callable($mockGenerateJWT)) {
            return call_user_func($mockGenerateJWT, $payload);
        }
        return $mockGenerateJWT;
    }
}

namespace {
    use PHPUnit\Framework\TestCase;

    // Importer la classe sous test en namespace global (adapter si nécessaire)
    require_once __DIR__ . '/../src/AuthAPI.php';

    // Alias du namespace mocké
    use Tests\AuthAPITest as Mock;

    final class AuthAPITest extends TestCase {

        protected function setUp(): void {
            Mock\$mockUserInfo = null;
            Mock\$mockGenerateJWT = null;
        }

        public function testInvalidEmailFormat() {
            $this->expectException(\InvalidArgumentException::class);
            \AuthAPI::login('bad-email', 'password');
        }

        public function testUserNotFound() {
            global $mockUserInfo;
            $mockUserInfo = null;

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionCode(404);

            \AuthAPI::login('test@example.com', 'password');
        }

        public function testInvalidPassword() {
            global $mockUserInfo;
            $mockUserInfo = [
                'id' => 1,
                'password' => password_hash('correctpassword', PASSWORD_DEFAULT)
            ];

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionCode(401);

            \AuthAPI::login('test@example.com', 'wrongpassword');
        }

        public function testFailedJWTGeneration() {
            global $mockUserInfo, $mockGenerateJWT;

            $mockUserInfo = [
                'id' => 1,
                'password' => password_hash('correctpassword', PASSWORD_DEFAULT)
            ];
            $mockGenerateJWT = false;

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionCode(500);

            \AuthAPI::login('test@example.com', 'correctpassword');
        }

        public function testSuccessReturnsJWT() {
            global $mockUserInfo, $mockGenerateJWT;

            $mockUserInfo = [
                'id' => 1,
                'password' => password_hash('correctpassword', PASSWORD_DEFAULT)
            ];
            $mockGenerateJWT = 'valid.jwt.token';

            $jwt = \AuthAPI::login('test@example.com', 'correctpassword');

            $this->assertEquals('valid.jwt.token', $jwt);
        }
    }
}
