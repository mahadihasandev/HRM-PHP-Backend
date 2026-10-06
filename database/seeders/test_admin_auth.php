<?php
function testAuthPost($url, $data, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    $allHeaders = array_merge(['Content-Type: application/json'], $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => json_decode($res, true)];
}

// 1. Unauthorized attempt by Engineer Tanvir Ahmed (SMT-0042)
$unauth = testAuthPost(
    'http://127.0.0.1:8000/api/hrm/permissions/employee/SMT-0051',
    ['permissions' => ['module.snd' => false], 'operator_id' => 'SMT-0042']
);
echo "1. Engineer SMT-0042 Attempt HTTP Code: " . $unauth['code'] . " - " . ($unauth['body']['message'] ?? 'null') . "\n";

// 2. Unauthorized attempt by Product Designer Nusrat Jahan (SMT-0026)
$unauthDesigner = testAuthPost(
    'http://127.0.0.1:8000/api/hrm/permissions/employee/SMT-0051',
    ['permissions' => ['module.snd' => false], 'operator_id' => 'SMT-0026']
);
echo "2. Designer SMT-0026 Attempt HTTP Code: " . $unauthDesigner['code'] . " - " . ($unauthDesigner['body']['message'] ?? 'null') . "\n";

// 3. Authorized attempt by Administrator SMT-0001
$auth = testAuthPost(
    'http://127.0.0.1:8000/api/hrm/permissions/employee/SMT-0051',
    ['permissions' => ['module.snd' => true], 'operator_id' => 'SMT-0001']
);
echo "3. Admin SMT-0001 Attempt HTTP Code: " . $auth['code'] . " - Success: " . ($auth['body']['status'] ? 'YES' : 'NO') . "\n";
