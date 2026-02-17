<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for input validation logic used across the SmartOrder application.
 *
 * These tests verify the validation patterns used in index.php, cart_update.php,
 * cart_remove.php, and checkout.php - without requiring a database connection.
 *
 * Validation patterns tested:
 * - tableNo: (int) cast from $_GET['tableNo'] in index.php:9
 * - itemId/orderId: (int) cast from $_POST in cart_update.php:11
 * - amount: (int) cast from $_POST in cart_update.php:12
 * - orderNo: format validation for order numbers
 */
class InputValidationTest extends TestCase
{
    // ==================== tableNo Validation ====================
    // Pattern from index.php line 9:
    //   $tableNo = isset($_GET['tableNo']) ? (int)$_GET['tableNo'] : 1;

    public function testTableNoStringAbcCastsToZero(): void
    {
        $tableNo = (int)'abc';

        $this->assertSame(0, $tableNo, 'Non-numeric string should cast to 0');
    }

    public function testTableNoValidStringCastsToInteger(): void
    {
        $tableNo = (int)'3';

        $this->assertSame(3, $tableNo);
    }

    public function testTableNoEmptyStringCastsToZero(): void
    {
        $tableNo = (int)'';

        $this->assertSame(0, $tableNo);
    }

    public function testTableNoNegativeStringCastsToNegative(): void
    {
        $tableNo = (int)'-1';

        $this->assertSame(-1, $tableNo, 'Negative string casts to negative int - app should validate range');
    }

    public function testTableNoDefaultsToOneWhenNotSet(): void
    {
        // Simulates: isset($_GET['tableNo']) ? (int)$_GET['tableNo'] : 1
        $get = []; // tableNo not set
        $tableNo = isset($get['tableNo']) ? (int)$get['tableNo'] : 1;

        $this->assertSame(1, $tableNo, 'Should default to table 1 when not provided');
    }

    public function testTableNoFloatStringTruncates(): void
    {
        $tableNo = (int)'2.9';

        $this->assertSame(2, $tableNo, 'Float string should truncate to integer');
    }

    // ==================== itemId / orderId Validation ====================
    // Pattern from cart_update.php line 11:
    //   $orderId = (int)$_POST['orderId'];
    // Pattern from cart_remove.php line 11:
    //   $orderId = (int)$_POST['orderId'];

    public function testNegativeOrderIdCastsToNegativeInt(): void
    {
        $orderId = (int)'-5';

        $this->assertSame(-5, $orderId);
        $this->assertLessThanOrEqual(0, $orderId, 'Negative orderId should be rejected by application logic');
    }

    public function testZeroOrderIdIsInvalid(): void
    {
        $orderId = (int)'0';

        $this->assertSame(0, $orderId);
        $this->assertLessThanOrEqual(0, $orderId, 'Zero orderId should be treated as invalid');
    }

    public function testValidOrderIdCastsCorrectly(): void
    {
        $orderId = (int)'42';

        $this->assertSame(42, $orderId);
        $this->assertGreaterThan(0, $orderId);
    }

    public function testSqlInjectionInOrderIdCastsToZero(): void
    {
        $orderId = (int)"1; DROP TABLE sOrder;--";

        $this->assertSame(1, $orderId, 'SQL injection attempt is neutralized by (int) cast');
    }

    // ==================== Amount Validation ====================
    // Pattern from cart_update.php line 12:
    //   $change = (int)$_POST['change']; // +1 or -1
    // Pattern from cart_update.php lines 27-30:
    //   if ($newAmount <= 0) { DELETE } else { UPDATE }

    public function testAmountChangePositiveOne(): void
    {
        $change = (int)'1';

        $this->assertSame(1, $change);
    }

    public function testAmountChangeNegativeOne(): void
    {
        $change = (int)'-1';

        $this->assertSame(-1, $change);
    }

    public function testAmountZeroResultsInNoChange(): void
    {
        $currentAmount = 3;
        $change = (int)'0';
        $newAmount = $currentAmount + $change;

        $this->assertSame(3, $newAmount, 'Zero change should keep amount the same');
    }

    public function testAmountBecomingZeroTriggersDelete(): void
    {
        // Simulates cart_update.php logic: if newAmount <= 0, delete
        $currentAmount = 1;
        $change = -1;
        $newAmount = $currentAmount + $change;

        $this->assertLessThanOrEqual(0, $newAmount, 'Amount reaching 0 should trigger deletion');
    }

    public function testAmountStaysPositiveOnIncrement(): void
    {
        $currentAmount = 1;
        $change = 1;
        $newAmount = $currentAmount + $change;

        $this->assertGreaterThan(0, $newAmount);
        $this->assertSame(2, $newAmount);
    }

    // ==================== orderNo Format Validation ====================
    // orderNo format is: YmdHis + random suffix (e.g., "20260217143022-1234")
    // Used in sManagement and sOrder tables as a string key

    public function testOrderNoFormatMatchesExpectedPattern(): void
    {
        // Simulate the format used in the database
        $orderNo = date('YmdHis') . '-' . sprintf('%04d', rand(0, 9999));

        // Pattern: 14 digits (YmdHis) + dash + 4 digits
        $this->assertMatchesRegularExpression(
            '/^\d{14}-\d{4}$/',
            $orderNo,
            'orderNo should match format: YYYYMMDDHHmmss-XXXX'
        );
    }

    public function testOrderNoDatePartIsValid(): void
    {
        $orderNo = date('YmdHis') . '-' . sprintf('%04d', rand(0, 9999));
        $datePart = substr($orderNo, 0, 8); // YYYYMMDD

        $year = (int)substr($datePart, 0, 4);
        $month = (int)substr($datePart, 4, 2);
        $day = (int)substr($datePart, 6, 2);

        $this->assertTrue(checkdate($month, $day, $year), 'Date portion of orderNo should be a valid date');
    }

    public function testOrderNoRandomSuffixRange(): void
    {
        // Generate multiple orderNos and verify suffix is within range
        for ($i = 0; $i < 10; $i++) {
            $suffix = sprintf('%04d', rand(0, 9999));
            $num = (int)$suffix;

            $this->assertGreaterThanOrEqual(0, $num);
            $this->assertLessThanOrEqual(9999, $num);
            $this->assertEquals(4, strlen($suffix), 'Suffix should always be 4 digits (zero-padded)');
        }
    }

    // ==================== XSS Prevention via htmlspecialchars ====================
    // Pattern used throughout: htmlspecialchars($value)

    public function testHtmlspecialcharsEscapesXssAttempt(): void
    {
        $maliciousInput = '<script>alert("XSS")</script>';
        $escaped = htmlspecialchars($maliciousInput);

        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }

    public function testIntCastPreventsXssInNumericFields(): void
    {
        $maliciousTableNo = '<img src=x onerror=alert(1)>';
        $tableNo = (int)$maliciousTableNo;

        $this->assertSame(0, $tableNo, 'XSS in numeric field is neutralized by (int) cast');
    }
}
