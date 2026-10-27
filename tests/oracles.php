<?php
// Exercise the restored providers without requiring live search engines.
chdir(__DIR__ . '/..');
require 'oracles/calc.php';
require 'oracles/encoder.php';
require 'oracles/numerics.php';
require 'oracles/time.php';

function check($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$calculator = new calculator();
check($calculator->check_query('2+3*4'), 'Calculator should recognize arithmetic');
check(!$calculator->check_query('42'), 'Plain numbers belong to the numeric oracle');
check(!$calculator->check_query('hello world'), 'Text is not arithmetic');
check($calculator->generate_response('2+3*4') === ['2+3*4 = ' => '14'], 'Arithmetic precedence');
check($calculator->generate_response('(2+3)*4') === ['(2+3)*4 = ' => '20'], 'Parentheses');

$encoder = new encoder();
check($encoder->check_query('sha256 hello'), 'Recognize hashing queries');
check($encoder->generate_response('sha256 hello') === ['sha256 hash' => hash('sha256', 'hello')], 'SHA-256 answer');
check($encoder->generate_response('rot13 hello') === ['rot13 encoded' => 'uryyb'], 'ROT13 answer');
check($encoder->generate_response('base64 aGVsbG8=')['base64 decoded'] === 'hello', 'Base64 decoding');

$numerics = new numerics();
foreach (['42', '0x2a', 'b101010'] as $query) {
    check($numerics->check_query($query), 'Recognize numeric query: ' . $query);
    $answer = $numerics->generate_response($query);
    check($answer['decimal (base 10)'] === '42', 'Decimal conversion');
    check($answer['hexadecimal (base 16)'] === '0x2a', 'Hex conversion');
    check($answer['binary (base 2)'] === 'b101010', 'Binary conversion');
}

$clock = new time();
check($clock->check_query('what time is it'), 'Recognize time query');
check(isset($clock->generate_response('what time is it')['The time in UTC']), 'UTC time response');

// Verify web.php still contains the integration restored from the removal commit.
$web = file_get_contents('web.php');
foreach (['new calculator()', 'new encoder()', 'new time()', 'new numerics()', '$oracle->check_query(', '$oracle->generate_response(', '$payload["left"] = $fortune;'] as $integration) {
    check(strpos($web, $integration) !== false, 'Missing search integration: ' . $integration);
}
echo "Oracle checks passed\n";
