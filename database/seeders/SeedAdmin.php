<?php
$pdo = new PDO('sqlite:backend/database/database.sqlite');
$stmt = $pdo->prepare("
INSERT INTO employees (
    id, employee_id, employee_full_id, name, email, phone, personal_phone,
    designation, department, company, company_id, status, blood_group, gender,
    marital_status, religion, basic_salary, house_rent, medical_allowance,
    conveyance, gross_salary, pf_deduction, tax_deduction, net_payable,
    created_at, updated_at
) VALUES (
    100, 100, 'SMT-0001', 'System Administrator', 'admin@smarterp.biz', '01711000001', '01711000001',
    'Chief Executive & Admin', 'Administration', 'Smart Technologies (BD) Ltd.', 7, 'Active', 'O+', 'Male',
    'Married', 'Islam', 120000, 60000, 12000,
    4000, 196000, 9996, 14600, 171404,
    datetime('now'), datetime('now')
)
ON CONFLICT(employee_full_id) DO UPDATE SET
    department = 'Administration',
    designation = 'Chief Executive & Admin';
");
$stmt->execute();
echo "SMT-0001 Administrator account verified.\n";
