<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for auth.php - Authentication & CSRF Protection System
 *
 * These tests verify the core security functions without requiring
 * a database connection. Session is started in setUp().
 */
class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset session for each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];

        // Start a fresh session (suppress headers-already-sent warning in CLI)
        @session_start();

        // Load auth.php (only once across all tests)
        require_once __DIR__ . '/../auth.php';
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    // ==================== CSRF Token Tests ====================

    public function testGenerateCsrfTokenReturns64CharHexString(): void
    {
        // Clear any existing token so generateCsrfToken creates a new one
        unset($_SESSION['csrf_token']);

        $token = generateCsrfToken();

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token), 'CSRF token should be 64 characters (32 bytes hex-encoded)');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token, 'CSRF token should be lowercase hex');
    }

    public function testGenerateCsrfTokenReturnsSameTokenOnSecondCall(): void
    {
        unset($_SESSION['csrf_token']);

        $token1 = generateCsrfToken();
        $token2 = generateCsrfToken();

        $this->assertSame($token1, $token2, 'generateCsrfToken should return the cached token on subsequent calls');
    }

    public function testRegenerateCsrfTokenCreatesNewToken(): void
    {
        unset($_SESSION['csrf_token']);

        $token1 = generateCsrfToken();
        $token2 = regenerateCsrfToken();

        $this->assertNotSame($token1, $token2, 'regenerateCsrfToken should create a different token');
        $this->assertEquals(64, strlen($token2));
    }

    // ==================== CSRF Validation Tests ====================

    public function testValidateCsrfTokenReturnsTrueForValidToken(): void
    {
        unset($_SESSION['csrf_token']);
        $token = generateCsrfToken();

        $this->assertTrue(validateCsrfToken($token), 'Should return true for matching token');
    }

    public function testValidateCsrfTokenReturnsFalseForInvalidToken(): void
    {
        unset($_SESSION['csrf_token']);
        generateCsrfToken();

        $this->assertFalse(validateCsrfToken('invalid_token_value'), 'Should return false for non-matching token');
    }

    public function testValidateCsrfTokenReturnsFalseForNull(): void
    {
        unset($_SESSION['csrf_token']);
        generateCsrfToken();

        $this->assertFalse(validateCsrfToken(null), 'Should return false for null token');
    }

    public function testValidateCsrfTokenReturnsFalseForEmptyString(): void
    {
        unset($_SESSION['csrf_token']);
        generateCsrfToken();

        $this->assertFalse(validateCsrfToken(''), 'Should return false for empty string');
    }

    public function testValidateCsrfTokenReturnsFalseWhenNoSessionToken(): void
    {
        unset($_SESSION['csrf_token']);

        $this->assertFalse(validateCsrfToken('some_token'), 'Should return false when no session token exists');
    }

    // ==================== Login Tests ====================

    public function testLoginReturnsFalseForWrongUsername(): void
    {
        $result = login('wronguser', 'admin123');

        $this->assertFalse($result, 'Should return false for wrong username');
    }

    public function testLoginReturnsFalseForWrongPassword(): void
    {
        $result = login('admin', 'wrongpassword');

        $this->assertFalse($result, 'Should return false for wrong password');
    }

    public function testLoginReturnsFalseForEmptyCredentials(): void
    {
        $result = login('', '');

        $this->assertFalse($result, 'Should return false for empty credentials');
    }

    public function testLoginReturnsTrueForCorrectCredentials(): void
    {
        $result = login('admin', 'admin123');

        $this->assertTrue($result, 'Should return true for correct admin credentials');
        $this->assertTrue($_SESSION['admin_logged_in']);
        $this->assertEquals('admin', $_SESSION['admin_username']);
        $this->assertArrayHasKey('login_time', $_SESSION);
    }

    // ==================== isLoggedIn Tests ====================

    public function testIsLoggedInReturnsFalseWhenNotLoggedIn(): void
    {
        unset($_SESSION['admin_logged_in']);

        $this->assertFalse(isLoggedIn(), 'Should return false when session has no login flag');
    }

    public function testIsLoggedInReturnsFalseWhenSetToNonTrue(): void
    {
        $_SESSION['admin_logged_in'] = 'yes';

        $this->assertFalse(isLoggedIn(), 'Should return false when login flag is not strictly true');
    }

    public function testIsLoggedInReturnsTrueWhenLoggedIn(): void
    {
        $_SESSION['admin_logged_in'] = true;

        $this->assertTrue(isLoggedIn(), 'Should return true when login flag is true');
    }

    // ==================== csrfField Tests ====================

    public function testCsrfFieldReturnsHiddenInput(): void
    {
        unset($_SESSION['csrf_token']);
        $field = csrfField();

        $this->assertStringContainsString('<input type="hidden"', $field);
        $this->assertStringContainsString('name="csrf_token"', $field);
        $this->assertStringContainsString('value="', $field);
    }

    // ==================== getAdminUsername Tests ====================

    public function testGetAdminUsernameReturnsDefaultWhenNotSet(): void
    {
        unset($_SESSION['admin_username']);

        $this->assertEquals('Admin', getAdminUsername());
    }

    public function testGetAdminUsernameReturnsSessionValue(): void
    {
        $_SESSION['admin_username'] = 'admin';

        $this->assertEquals('admin', getAdminUsername());
    }
}
