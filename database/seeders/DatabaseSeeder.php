<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with extensive realistic Bangladeshi HRM data.
     */
    public function run(): void
    {
        $now = now();

        // 0. Default Users
        User::firstOrCreate(
            ['email' => 'admin@smarterp.biz'],
            [
                'name' => 'HR Administrator',
                'password' => bcrypt('password123'),
            ]
        );
        User::firstOrCreate(
            ['email' => 'halim@smarterp.biz'],
            [
                'name' => 'Abdul Halim',
                'password' => bcrypt('password123'),
            ]
        );

        // 1. Seed 60+ Employees across all corporate departments
        $employeesData = [
            // Sales & Distribution
            [
                'employee_id' => 479,
                'employee_full_id' => 'SMT-0051',
                'name' => 'Abdul Halim',
                'email' => 'halim@smarterp.biz',
                'phone' => '01717186089',
                'personal_phone' => '01717186089',
                'designation' => 'Senior Field Sales Manager',
                'department' => 'Sales & Distribution',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'B+',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1992-06-15',
                'joining_date' => '2020-02-01',
                'present_address' => 'House 14, Road 4, Sector 7, Uttara, Dhaka',
                'permanent_address' => 'Chittagong Sadar, Chittagong',
                'father_name' => 'Md. Shamsul Huda',
                'mother_name' => 'Begum Rokeya',
                'guardian_name' => 'Md. Shamsul Huda',
                'guardian_phone' => '01711223344',
                'bank_name' => 'Eastern Bank PLC',
                'bank_account_no' => '1081250987621',
                'branch_name' => 'Banani Branch',
                'routing_name' => '095260842',
                'basic_salary' => 55000,
                'house_rent' => 27500,
                'medical_allowance' => 5500,
                'conveyance' => 4000,
                'gross_salary' => 92000,
                'pf_deduction' => 5500,
                'tax_deduction' => 4200,
                'net_payable' => 82300,
            ],
            [
                'employee_id' => 1050,
                'employee_full_id' => 'SMT-0104',
                'name' => 'Kamrul Hasan',
                'email' => 'kamrul.sales@smarterp.biz',
                'phone' => '01755667788',
                'personal_phone' => '01755667788',
                'designation' => 'Area Territory Sales Executive',
                'department' => 'Sales & Distribution',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'A+',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1994-08-25',
                'joining_date' => '2021-03-01',
                'present_address' => 'Tejgaon Industrial Area, Dhaka',
                'permanent_address' => 'Munshiganj Sadar, Munshiganj',
                'father_name' => 'Monirul Islam',
                'mother_name' => 'Selina Akhter',
                'guardian_name' => 'Monirul Islam',
                'guardian_phone' => '01711334455',
                'bank_name' => 'Eastern Bank PLC',
                'bank_account_no' => '1081253456789',
                'branch_name' => 'Tejgaon Branch',
                'routing_name' => '095260456',
                'basic_salary' => 42000,
                'house_rent' => 21000,
                'medical_allowance' => 4200,
                'conveyance' => 3500,
                'gross_salary' => 70700,
                'pf_deduction' => 4200,
                'tax_deduction' => 2200,
                'net_payable' => 64300,
            ],
            [
                'employee_id' => 1051,
                'employee_full_id' => 'SMT-0105',
                'name' => 'Zubair Hossain',
                'email' => 'zubair.ctg@smarterp.biz',
                'phone' => '01866778899',
                'personal_phone' => '01866778899',
                'designation' => 'Regional Distribution Lead',
                'department' => 'Sales & Distribution',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'O+',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1991-03-18',
                'joining_date' => '2019-11-15',
                'present_address' => 'Agrabad Commercial Area, Chittagong',
                'permanent_address' => 'Feni Sadar, Feni',
                'father_name' => 'M. A. Hashem',
                'mother_name' => 'Nurjahan Begum',
                'guardian_name' => 'M. A. Hashem',
                'guardian_phone' => '01822446688',
                'bank_name' => 'Islami Bank Bangladesh PLC',
                'bank_account_no' => '2050123456789',
                'branch_name' => 'Agrabad Branch',
                'routing_name' => '125260891',
                'basic_salary' => 58000,
                'house_rent' => 29000,
                'medical_allowance' => 5800,
                'conveyance' => 4500,
                'gross_salary' => 97300,
                'pf_deduction' => 5800,
                'tax_deduction' => 4900,
                'net_payable' => 86600,
            ],
            [
                'employee_id' => 1057,
                'employee_full_id' => 'SMT-0128',
                'name' => 'Tariqul Alam',
                'email' => 'tariqul.field@smarterp.biz',
                'phone' => '01633445577',
                'personal_phone' => '01633445577',
                'designation' => 'Senior Field Sales Officer',
                'department' => 'Sales & Distribution',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'B+',
                'gender' => 'Male',
                'marital_status' => 'Single',
                'religion' => 'Islam',
                'date_of_birth' => '1996-07-22',
                'joining_date' => '2023-02-15',
                'present_address' => 'GEC Circle, Nasirabad, Chittagong',
                'permanent_address' => 'Cox\'s Bazar Sadar, Cox\'s Bazar',
                'father_name' => 'Nurul Alam',
                'mother_name' => 'Hamida Begum',
                'guardian_name' => 'Nurul Alam',
                'guardian_phone' => '01611223344',
                'bank_name' => 'BRAC Bank PLC',
                'bank_account_no' => '1501208901234',
                'branch_name' => 'Agrabad Branch',
                'routing_name' => '060261556',
                'basic_salary' => 38000,
                'house_rent' => 19000,
                'medical_allowance' => 3800,
                'conveyance' => 3500,
                'gross_salary' => 64300,
                'pf_deduction' => 3800,
                'tax_deduction' => 1800,
                'net_payable' => 58700,
            ],
            [
                'employee_id' => 1061,
                'employee_full_id' => 'SMT-0145',
                'name' => 'Anisur Rahman',
                'email' => 'anisur.sales@smarterp.biz',
                'phone' => '01718899001',
                'personal_phone' => '01718899001',
                'designation' => 'Territory Sales Officer',
                'department' => 'Sales & Distribution',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'O+',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1993-09-10',
                'joining_date' => '2021-08-01',
                'present_address' => 'Bogra Sadar, Bogra',
                'permanent_address' => 'Sirajganj Sadar, Sirajganj',
                'father_name' => 'Mojibur Rahman',
                'mother_name' => 'Salma Khatun',
                'guardian_name' => 'Mojibur Rahman',
                'guardian_phone' => '01711009988',
                'bank_name' => 'Dutch-Bangla Bank PLC',
                'bank_account_no' => '1151203456781',
                'branch_name' => 'Bogra Branch',
                'routing_name' => '090261902',
                'basic_salary' => 36000,
                'house_rent' => 18000,
                'medical_allowance' => 3600,
                'conveyance' => 3500,
                'gross_salary' => 61100,
                'pf_deduction' => 3600,
                'tax_deduction' => 1500,
                'net_payable' => 56000,
            ],

            // Human Resources & People Operations
            [
                'employee_id' => 1046,
                'employee_full_id' => 'SMT-0007',
                'name' => 'Ariful Islam',
                'email' => 'ariful.hr@smarterp.biz',
                'phone' => '01817603163',
                'personal_phone' => '01817603163',
                'designation' => 'Senior HR Specialist',
                'department' => 'Human Resources',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'O+',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1990-04-12',
                'joining_date' => '2019-07-15',
                'present_address' => 'Block C, Bashundhara R/A, Dhaka',
                'permanent_address' => 'Comilla Sadar, Comilla',
                'father_name' => 'Abdul Mannan',
                'mother_name' => 'Fatema Begum',
                'guardian_name' => 'Abdul Mannan',
                'guardian_phone' => '01811223344',
                'bank_name' => 'Dutch-Bangla Bank PLC',
                'bank_account_no' => '1151209876342',
                'branch_name' => 'Gulshan Branch',
                'routing_name' => '090261234',
                'basic_salary' => 60000,
                'house_rent' => 30000,
                'medical_allowance' => 6000,
                'conveyance' => 4000,
                'gross_salary' => 100000,
                'pf_deduction' => 6000,
                'tax_deduction' => 5500,
                'net_payable' => 88500,
            ],
            [
                'employee_id' => 1055,
                'employee_full_id' => 'SMT-0115',
                'name' => 'Farhana Yasmin',
                'email' => 'farhana.hr@smarterp.biz',
                'phone' => '01811223399',
                'personal_phone' => '01811223399',
                'designation' => 'Talent Acquisition Lead',
                'department' => 'Human Resources',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'O+',
                'gender' => 'Female',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1993-01-30',
                'joining_date' => '2021-04-15',
                'present_address' => 'Banasree Block D, Dhaka',
                'permanent_address' => 'Kushtia Sadar, Kushtia',
                'father_name' => 'Matiur Rahman',
                'mother_name' => 'Anwara Begum',
                'guardian_name' => 'Matiur Rahman',
                'guardian_phone' => '01866771122',
                'bank_name' => 'Dutch-Bangla Bank PLC',
                'bank_account_no' => '1151207890123',
                'branch_name' => 'Rampura Branch',
                'routing_name' => '090261345',
                'basic_salary' => 54000,
                'house_rent' => 27000,
                'medical_allowance' => 5400,
                'conveyance' => 4000,
                'gross_salary' => 90400,
                'pf_deduction' => 5400,
                'tax_deduction' => 4100,
                'net_payable' => 80900,
            ],
            [
                'employee_id' => 1062,
                'employee_full_id' => 'SMT-0150',
                'name' => 'Nazia Farzana',
                'email' => 'nazia.hr@smarterp.biz',
                'phone' => '01844556677',
                'personal_phone' => '01844556677',
                'designation' => 'HR Operations & Compliance Officer',
                'department' => 'Human Resources',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'B+',
                'gender' => 'Female',
                'marital_status' => 'Single',
                'religion' => 'Islam',
                'date_of_birth' => '1997-04-14',
                'joining_date' => '2023-05-01',
                'present_address' => 'Dhanmondi 27, Dhaka',
                'permanent_address' => 'Mymensingh Sadar, Mymensingh',
                'father_name' => 'Enayet Karim',
                'mother_name' => 'Suraiya Parveen',
                'guardian_name' => 'Enayet Karim',
                'guardian_phone' => '01822339900',
                'bank_name' => 'BRAC Bank PLC',
                'bank_account_no' => '1501201122334',
                'branch_name' => 'Dhanmondi Branch',
                'routing_name' => '060261987',
                'basic_salary' => 40000,
                'house_rent' => 20000,
                'medical_allowance' => 4000,
                'conveyance' => 3500,
                'gross_salary' => 67500,
                'pf_deduction' => 4000,
                'tax_deduction' => 2000,
                'net_payable' => 61500,
            ],

            // Engineering & Software Architecture
            [
                'employee_id' => 1047,
                'employee_full_id' => 'SMT-0026',
                'name' => 'Nusrat Jahan',
                'email' => 'nusrat.eng@smarterp.biz',
                'phone' => '01823456789',
                'personal_phone' => '01823456789',
                'designation' => 'Lead UI/UX Architect',
                'department' => 'Product Design',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'A+',
                'gender' => 'Female',
                'marital_status' => 'Single',
                'religion' => 'Islam',
                'date_of_birth' => '1995-11-20',
                'joining_date' => '2021-01-10',
                'present_address' => 'House 8, Road 2, Dhanmondi, Dhaka',
                'permanent_address' => 'Sylhet Sadar, Sylhet',
                'father_name' => 'Md. Jahangir Alam',
                'mother_name' => 'Shahana Parvin',
                'guardian_name' => 'Md. Jahangir Alam',
                'guardian_phone' => '01899887766',
                'bank_name' => 'BRAC Bank PLC',
                'bank_account_no' => '1501209876543',
                'branch_name' => 'Dhanmondi Branch',
                'routing_name' => '060261987',
                'basic_salary' => 65000,
                'house_rent' => 32500,
                'medical_allowance' => 6500,
                'conveyance' => 5000,
                'gross_salary' => 109000,
                'pf_deduction' => 6500,
                'tax_deduction' => 6800,
                'net_payable' => 95700,
            ],
            [
                'employee_id' => 1048,
                'employee_full_id' => 'SMT-0042',
                'name' => 'Tanvir Ahmed',
                'email' => 'tanvir.tech@smarterp.biz',
                'phone' => '01934567890',
                'personal_phone' => '01934567890',
                'designation' => 'Principal Backend Engineer',
                'department' => 'Engineering',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'B+',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1988-08-05',
                'joining_date' => '2017-09-01',
                'present_address' => 'Avenue 4, Mirpur DOHS, Dhaka',
                'permanent_address' => 'Rajshahi Sadar, Rajshahi',
                'father_name' => 'Prof. Azizur Rahman',
                'mother_name' => 'Laila Arjumand',
                'guardian_name' => 'Prof. Azizur Rahman',
                'guardian_phone' => '01911224455',
                'bank_name' => 'City Bank PLC',
                'bank_account_no' => '2201987654321',
                'branch_name' => 'Mirpur Branch',
                'routing_name' => '070261456',
                'basic_salary' => 85000,
                'house_rent' => 42500,
                'medical_allowance' => 8500,
                'conveyance' => 6000,
                'gross_salary' => 142000,
                'pf_deduction' => 8500,
                'tax_deduction' => 12500,
                'net_payable' => 121000,
            ],
            [
                'employee_id' => 1054,
                'employee_full_id' => 'SMT-0112',
                'name' => 'Tahmina Akter',
                'email' => 'tahmina.qa@smarterp.biz',
                'phone' => '01799001122',
                'personal_phone' => '01799001122',
                'designation' => 'Senior QA Automation Engineer',
                'department' => 'Engineering',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'B+',
                'gender' => 'Female',
                'marital_status' => 'Single',
                'religion' => 'Islam',
                'date_of_birth' => '1995-12-08',
                'joining_date' => '2022-06-01',
                'present_address' => 'Mohakhali DOHS, Dhaka',
                'permanent_address' => 'Barisal Sadar, Barisal',
                'father_name' => 'Tariqul Islam',
                'mother_name' => 'Monowara Begum',
                'guardian_name' => 'Tariqul Islam',
                'guardian_phone' => '01755661122',
                'bank_name' => 'BRAC Bank PLC',
                'bank_account_no' => '1501205678901',
                'branch_name' => 'Gulshan Branch',
                'routing_name' => '060261112',
                'basic_salary' => 52000,
                'house_rent' => 26000,
                'medical_allowance' => 5200,
                'conveyance' => 4000,
                'gross_salary' => 87200,
                'pf_deduction' => 5200,
                'tax_deduction' => 3800,
                'net_payable' => 78200,
            ],
            [
                'employee_id' => 1063,
                'employee_full_id' => 'SMT-0155',
                'name' => 'Sajjadul Karim',
                'email' => 'sajjad.devops@smarterp.biz',
                'phone' => '01722334411',
                'personal_phone' => '01722334411',
                'designation' => 'DevOps & Cloud Infrastructure Lead',
                'department' => 'Engineering',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'AB+',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1991-05-19',
                'joining_date' => '2020-10-01',
                'present_address' => 'Niketan, Gulshan-1, Dhaka',
                'permanent_address' => 'Gazipur Sadar, Gazipur',
                'father_name' => 'Rezaul Karim',
                'mother_name' => 'Rasheda Akhter',
                'guardian_name' => 'Rezaul Karim',
                'guardian_phone' => '01711998877',
                'bank_name' => 'Eastern Bank PLC',
                'bank_account_no' => '1081258899001',
                'branch_name' => 'Gulshan Branch',
                'routing_name' => '095260842',
                'basic_salary' => 72000,
                'house_rent' => 36000,
                'medical_allowance' => 7200,
                'conveyance' => 5000,
                'gross_salary' => 120200,
                'pf_deduction' => 7200,
                'tax_deduction' => 8500,
                'net_payable' => 104500,
            ],
            [
                'employee_id' => 1064,
                'employee_full_id' => 'SMT-0160',
                'name' => 'Sadia Afroz',
                'email' => 'sadia.frontend@smarterp.biz',
                'phone' => '01955667788',
                'personal_phone' => '01955667788',
                'designation' => 'Frontend UI Engineer',
                'department' => 'Engineering',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'O+',
                'gender' => 'Female',
                'marital_status' => 'Single',
                'religion' => 'Islam',
                'date_of_birth' => '1998-02-14',
                'joining_date' => '2023-07-01',
                'present_address' => 'Lalmatia Block B, Dhaka',
                'permanent_address' => 'Rangpur Sadar, Rangpur',
                'father_name' => 'Afzal Hossain',
                'mother_name' => 'Meherun Nesa',
                'guardian_name' => 'Afzal Hossain',
                'guardian_phone' => '01911447788',
                'bank_name' => 'City Bank PLC',
                'bank_account_no' => '2201984455667',
                'branch_name' => 'Dhanmondi Branch',
                'routing_name' => '070261334',
                'basic_salary' => 45000,
                'house_rent' => 22500,
                'medical_allowance' => 4500,
                'conveyance' => 4000,
                'gross_salary' => 76000,
                'pf_deduction' => 4500,
                'tax_deduction' => 2700,
                'net_payable' => 68800,
            ],

            // Finance & Accounts
            [
                'employee_id' => 1049,
                'employee_full_id' => 'SMT-0089',
                'name' => 'Sadia Rahman',
                'email' => 'sadia.fin@smarterp.biz',
                'phone' => '01645678901',
                'personal_phone' => '01645678901',
                'designation' => 'Senior Financial Controller',
                'department' => 'Finance & Accounts',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'On Leave',
                'blood_group' => 'AB+',
                'gender' => 'Female',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1991-02-28',
                'joining_date' => '2018-11-01',
                'present_address' => 'Plot 12, Road 9, Gulshan-2, Dhaka',
                'permanent_address' => 'Khulna Sadar, Khulna',
                'father_name' => 'Mahbubul Haque',
                'mother_name' => 'Nasreen Akhtar',
                'guardian_name' => 'Mahbubul Haque',
                'guardian_phone' => '01677889900',
                'bank_name' => 'Standard Chartered Bank BD',
                'bank_account_no' => '0101987654321',
                'branch_name' => 'Gulshan Branch',
                'routing_name' => '215260112',
                'basic_salary' => 70000,
                'house_rent' => 35000,
                'medical_allowance' => 7000,
                'conveyance' => 5000,
                'gross_salary' => 117000,
                'pf_deduction' => 7000,
                'tax_deduction' => 8200,
                'net_payable' => 101800,
            ],
            [
                'employee_id' => 1056,
                'employee_full_id' => 'SMT-0120',
                'name' => 'Mahbubur Rahman',
                'email' => 'mahbub.fin@smarterp.biz',
                'phone' => '01922334488',
                'personal_phone' => '01922334488',
                'designation' => 'Head of Credit Control',
                'department' => 'Finance & Accounts',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'A+',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1987-11-19',
                'joining_date' => '2016-08-01',
                'present_address' => 'Shantinagar Eastern Point, Dhaka',
                'permanent_address' => 'Pabna Sadar, Pabna',
                'father_name' => 'Lutfar Rahman',
                'mother_name' => 'Khaleda Begum',
                'guardian_name' => 'Lutfar Rahman',
                'guardian_phone' => '01944551122',
                'bank_name' => 'Islami Bank Bangladesh PLC',
                'bank_account_no' => '2050124567890',
                'branch_name' => 'Motijheel Branch',
                'routing_name' => '125260112',
                'basic_salary' => 68000,
                'house_rent' => 34000,
                'medical_allowance' => 6800,
                'conveyance' => 5000,
                'gross_salary' => 113800,
                'pf_deduction' => 6800,
                'tax_deduction' => 7800,
                'net_payable' => 99200,
            ],
            [
                'employee_id' => 1058,
                'employee_full_id' => 'SMT-0135',
                'name' => 'Shamima Nasrin',
                'email' => 'shamima.acct@smarterp.biz',
                'phone' => '01744556600',
                'personal_phone' => '01744556600',
                'designation' => 'Senior Payroll Specialist',
                'department' => 'Finance & Accounts',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'AB+',
                'gender' => 'Female',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1992-09-04',
                'joining_date' => '2020-07-01',
                'present_address' => 'Malibagh Chowdhury Para, Dhaka',
                'permanent_address' => 'Narayanganj Sadar, Narayanganj',
                'father_name' => 'Golam Mostafa',
                'mother_name' => 'Rasheda Begum',
                'guardian_name' => 'Golam Mostafa',
                'guardian_phone' => '01711882233',
                'bank_name' => 'Eastern Bank PLC',
                'bank_account_no' => '1081259900112',
                'branch_name' => 'Kakrail Branch',
                'routing_name' => '095260334',
                'basic_salary' => 48000,
                'house_rent' => 24000,
                'medical_allowance' => 4800,
                'conveyance' => 4000,
                'gross_salary' => 80800,
                'pf_deduction' => 4800,
                'tax_deduction' => 3100,
                'net_payable' => 72900,
            ],

            // Operations, Factory & Assembly Plants
            [
                'employee_id' => 1052,
                'employee_full_id' => 'SMT-0103',
                'name' => 'Rafiqul Islam',
                'email' => 'rafiqul.ops@smarterp.biz',
                'phone' => '01977889900',
                'personal_phone' => '01977889900',
                'designation' => 'Plant Operations Supervisor',
                'department' => 'Operations',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'O+',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1989-10-05',
                'joining_date' => '2018-04-01',
                'present_address' => 'Tongi Industrial Area, Gazipur',
                'permanent_address' => 'Tangail Sadar, Tangail',
                'father_name' => 'Khorshed Alam',
                'mother_name' => 'Jahanara Begum',
                'guardian_name' => 'Khorshed Alam',
                'guardian_phone' => '01933445566',
                'bank_name' => 'Eastern Bank PLC',
                'bank_account_no' => '1081254567890',
                'branch_name' => 'Gazipur Branch',
                'routing_name' => '095260112',
                'basic_salary' => 52000,
                'house_rent' => 26000,
                'medical_allowance' => 5200,
                'conveyance' => 4000,
                'gross_salary' => 87200,
                'pf_deduction' => 5200,
                'tax_deduction' => 3800,
                'net_payable' => 78200,
            ],
            [
                'employee_id' => 1053,
                'employee_full_id' => 'SMT-0105B',
                'name' => 'Nasir Uddin',
                'email' => 'nasir.sc@smarterp.biz',
                'phone' => '01688990011',
                'personal_phone' => '01688990011',
                'designation' => 'Supply Chain Coordinator',
                'department' => 'Supply Chain',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'AB-',
                'gender' => 'Male',
                'marital_status' => 'Single',
                'religion' => 'Islam',
                'date_of_birth' => '1997-05-14',
                'joining_date' => '2023-01-10',
                'present_address' => 'Savar EPZ, Savar, Dhaka',
                'permanent_address' => 'Faridpur Sadar, Faridpur',
                'father_name' => 'Afzal Hossain',
                'mother_name' => 'Rabeya Begum',
                'guardian_name' => 'Afzal Hossain',
                'guardian_phone' => '01644556677',
                'bank_name' => 'City Bank PLC',
                'bank_account_no' => '2201981234567',
                'branch_name' => 'Savar Branch',
                'routing_name' => '070261334',
                'basic_salary' => 35000,
                'house_rent' => 17500,
                'medical_allowance' => 3500,
                'conveyance' => 3000,
                'gross_salary' => 59000,
                'pf_deduction' => 3500,
                'tax_deduction' => 1400,
                'net_payable' => 54100,
            ],
            [
                'employee_id' => 1059,
                'employee_full_id' => 'SMT-0140',
                'name' => 'Kazi Golam Kibria',
                'email' => 'kibria.logistics@smarterp.biz',
                'phone' => '01511223344',
                'personal_phone' => '01511223344',
                'designation' => 'Fleet Logistics Manager',
                'department' => 'Supply Chain',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'A+',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'religion' => 'Islam',
                'date_of_birth' => '1985-04-18',
                'joining_date' => '2015-02-01',
                'present_address' => 'Tejgaon Truck Stand Road, Dhaka',
                'permanent_address' => 'Jessore Sadar, Jessore',
                'father_name' => 'Kazi Nurul Huda',
                'mother_name' => 'Salma Begum',
                'guardian_name' => 'Kazi Nurul Huda',
                'guardian_phone' => '01511009988',
                'bank_name' => 'Sonali Bank PLC',
                'bank_account_no' => '3301256789012',
                'branch_name' => 'Tejgaon Branch',
                'routing_name' => '200260456',
                'basic_salary' => 56000,
                'house_rent' => 28000,
                'medical_allowance' => 5600,
                'conveyance' => 4500,
                'gross_salary' => 94100,
                'pf_deduction' => 5600,
                'tax_deduction' => 4500,
                'net_payable' => 84000,
            ],
            [
                'employee_id' => 1060,
                'employee_full_id' => 'SMT-0142',
                'name' => 'Jannatul Ferdous',
                'email' => 'jannat.qa@smarterp.biz',
                'phone' => '01733445566',
                'personal_phone' => '01733445566',
                'designation' => 'Quality Assurance Chemist',
                'department' => 'Operations',
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => 'B+',
                'gender' => 'Female',
                'marital_status' => 'Single',
                'religion' => 'Islam',
                'date_of_birth' => '1996-03-12',
                'joining_date' => '2022-09-15',
                'present_address' => 'Joydebpur, Gazipur',
                'permanent_address' => 'Narsingdi Sadar, Narsingdi',
                'father_name' => 'Shahidul Islam',
                'mother_name' => 'Nasreen Parveen',
                'guardian_name' => 'Shahidul Islam',
                'guardian_phone' => '01711227788',
                'bank_name' => 'Prime Bank PLC',
                'bank_account_no' => '1201985678901',
                'branch_name' => 'Gazipur Branch',
                'routing_name' => '170260112',
                'basic_salary' => 38000,
                'house_rent' => 19000,
                'medical_allowance' => 3800,
                'conveyance' => 3500,
                'gross_salary' => 64300,
                'pf_deduction' => 3800,
                'tax_deduction' => 1800,
                'net_payable' => 58700,
            ],
        ];

        // Generate additional 45 employees programmatically with authentic BD data
        $firstNames = ['Md. Alamin', 'Sharmin', 'Khandaker Shafi', 'M. A. Taher', 'Fazlul Huq', 'Farhan Tanvir', 'Mahfuzur Rahman', 'Shahidul Alam', 'Md. Rezaul Karim', 'Enamul Haque', 'Ziaur Rahman', 'Syed Ahsan', 'Haji Sirajul', 'Rashidul Haque', 'Mostafa Kamal', 'Sayedul Arefin', 'Tasnim', 'Rifat', 'Mehedi', 'Ismail', 'Zakir', 'Nurul', 'Ahsan', 'Masum', 'Jahangir', 'Monir', 'Sumon', 'Mamun', 'Rubel', 'Saiful', 'Selim', 'Ripon', 'Biplob', 'Shohel', 'Habib', 'Golam', 'Belal', 'Asad', 'Siraj', 'Mizan', 'Shahab', 'Jashim', 'Kamal', 'Harun', 'Anwar'];
        $lastNames = ['Hossain', 'Akter', 'Islam', 'Chowdhury', 'Ahmed', 'Rahman', 'Uddin', 'Khan', 'Sikder', 'Miah', 'Talukder', 'Bhuiyan', 'Molla', 'Mirza', 'Patwary', 'Dewan', 'Gazi', 'Sarkar', 'Howlader', 'Majumdar'];
        $depts = [
            ['Sales & Distribution', 'Field Territory Officer', 35000],
            ['Sales & Distribution', 'Senior Key Account Officer', 48000],
            ['Engineering', 'Full Stack Developer', 55000],
            ['Engineering', 'DevOps Systems Admin', 62000],
            ['Human Resources', 'HR Officer (Employee Relations)', 38000],
            ['Finance & Accounts', 'Accounts Executive', 42000],
            ['Supply Chain', 'Warehouse In-Charge', 40000],
            ['Operations', 'Production Line Lead', 45000],
            ['Product Design', 'UI Designer', 50000],
            ['Legal & Compliance', 'Legal Compliance Officer', 58000],
        ];
        $banks = [
            ['BRAC Bank PLC', '060261987'],
            ['Eastern Bank PLC', '095260842'],
            ['Dutch-Bangla Bank PLC', '090261234'],
            ['City Bank PLC', '070261456'],
            ['Islami Bank Bangladesh PLC', '125260112'],
            ['Prime Bank PLC', '170260112'],
            ['Mutual Trust Bank PLC', '145260223'],
        ];

        for ($i = 0; $i < 45; $i++) {
            $fName = $firstNames[$i % count($firstNames)];
            $lName = $lastNames[$i % count($lastNames)];
            $fullName = "{$fName} {$lName}";
            $deptInfo = $depts[$i % count($depts)];
            $bankInfo = $banks[$i % count($banks)];
            $empId = 2000 + $i;
            $code = 'SMT-' . str_pad((string)(200 + $i), 4, '0', STR_PAD_LEFT);
            $basic = $deptInfo[2];
            $houseRent = $basic * 0.50;
            $medical = $basic * 0.10;
            $conveyance = 4000;
            $gross = $basic + $houseRent + $medical + $conveyance;
            $pf = $basic * 0.10;
            $tax = round($gross * 0.05);
            $net = $gross - $pf - $tax;

            $employeesData[] = [
                'employee_id' => $empId,
                'employee_full_id' => $code,
                'name' => $fullName,
                'email' => strtolower(str_replace([' ', '.'], ['', ''], $fName)) . ($i + 1) . '@smarterp.biz',
                'phone' => '017' . rand(10000000, 99999999),
                'personal_phone' => '017' . rand(10000000, 99999999),
                'designation' => $deptInfo[1],
                'department' => $deptInfo[0],
                'company' => 'Smart Technologies (BD) Ltd.',
                'company_id' => 7,
                'status' => 'Active',
                'blood_group' => ['A+', 'B+', 'O+', 'AB+'][rand(0, 3)],
                'gender' => strpos($fullName, 'Sharmin') !== false || strpos($fullName, 'Tasnim') !== false || strpos($fullName, 'Akter') !== false ? 'Female' : 'Male',
                'marital_status' => rand(0, 1) ? 'Married' : 'Single',
                'religion' => 'Islam',
                'date_of_birth' => '199' . rand(0, 8) . '-0' . rand(1, 9) . '-15',
                'joining_date' => '202' . rand(0, 4) . '-0' . rand(1, 9) . '-01',
                'present_address' => 'Sector ' . rand(1, 14) . ', Uttara / Mirpur, Dhaka',
                'permanent_address' => 'District Sadar, Bangladesh',
                'father_name' => 'Md. ' . $lastNames[rand(0, count($lastNames) - 1)] . ' Ali',
                'mother_name' => 'Fatema Begum',
                'guardian_name' => 'Father',
                'guardian_phone' => '018' . rand(10000000, 99999999),
                'bank_name' => $bankInfo[0],
                'bank_account_no' => (string) rand(1000000000000, 9999999999999),
                'branch_name' => 'Corporate Principal Branch',
                'routing_name' => $bankInfo[1],
                'basic_salary' => $basic,
                'house_rent' => $houseRent,
                'medical_allowance' => $medical,
                'conveyance' => $conveyance,
                'gross_salary' => $gross,
                'pf_deduction' => $pf,
                'tax_deduction' => $tax,
                'net_payable' => $net,
            ];
        }

        foreach ($employeesData as $emp) {
            DB::table('employees')->updateOrInsert(
                ['employee_id' => $emp['employee_id']],
                array_merge($emp, ['created_at' => $now, 'updated_at' => $now])
            );

            // Ensure every employee is also a user who can log in & punch
            $userEmail = !empty($emp['email']) ? $emp['email'] : ($emp['employee_full_id'] . '@smarterp.biz');
            User::updateOrCreate(
                ['email' => $userEmail],
                [
                    'name' => $emp['name'],
                    'password' => bcrypt('password123'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }


        // 2. Seed 35+ HR Loans & Advances
        $loansData = [
            [
                'id' => 1,
                'employee_id' => 479,
                'employee_full_id' => 'SMT-0051',
                'employee_name' => 'Abdul Halim',
                'amount' => 100000,
                'installment_count' => 10,
                'monthly_installment' => 10000,
                'applicable_month' => '2026-11',
                'purpose' => 'Emergency medical expenses for father hospitalization at Square Hospital',
                'cash_value' => 50000,
                'bank_value' => 50000,
                'status' => 'Active',
                'applied_at' => '2026-09-15',
            ],
            [
                'id' => 2,
                'employee_id' => 479,
                'employee_full_id' => 'SMT-0051',
                'employee_name' => 'Abdul Halim',
                'amount' => 25000,
                'installment_count' => 5,
                'monthly_installment' => 5000,
                'applicable_month' => '2026-10',
                'purpose' => 'Autumn Quarter Festival Advance (Eid-e-Miladunnabi & Puja)',
                'cash_value' => 10000,
                'bank_value' => 15000,
                'status' => 'Approved',
                'applied_at' => '2026-09-20',
            ],
            [
                'id' => 3,
                'employee_id' => 1046,
                'employee_full_id' => 'SMT-0007',
                'employee_name' => 'Ariful Islam',
                'amount' => 150000,
                'installment_count' => 15,
                'monthly_installment' => 10000,
                'applicable_month' => '2026-08',
                'purpose' => 'Apartment interior renovation and household relocation advance',
                'cash_value' => 50000,
                'bank_value' => 100000,
                'status' => 'Active',
                'applied_at' => '2026-07-28',
            ],
            [
                'id' => 4,
                'employee_id' => 1047,
                'employee_full_id' => 'SMT-0026',
                'employee_name' => 'Nusrat Jahan',
                'amount' => 80000,
                'installment_count' => 8,
                'monthly_installment' => 10000,
                'applicable_month' => '2026-11',
                'purpose' => 'High-end design workstation MacBook M3 upgrade',
                'cash_value' => 30000,
                'bank_value' => 50000,
                'status' => 'Pending',
                'applied_at' => '2026-10-01',
            ],
            [
                'id' => 5,
                'employee_id' => 1048,
                'employee_full_id' => 'SMT-0042',
                'employee_name' => 'Tanvir Ahmed',
                'amount' => 200000,
                'installment_count' => 20,
                'monthly_installment' => 10000,
                'applicable_month' => '2026-06',
                'purpose' => 'Land registration fee and legal conveyance at Purbachal',
                'cash_value' => 100000,
                'bank_value' => 100000,
                'status' => 'Active',
                'applied_at' => '2026-05-12',
            ],
            [
                'id' => 6,
                'employee_id' => 1050,
                'employee_full_id' => 'SMT-0104',
                'employee_name' => 'Kamrul Hasan',
                'amount' => 60000,
                'installment_count' => 12,
                'monthly_installment' => 5000,
                'applicable_month' => '2026-11',
                'purpose' => 'Yamaha FZ-S motorcycle servicing and tyre replacement for field tour',
                'cash_value' => 20000,
                'bank_value' => 40000,
                'status' => 'Approved',
                'applied_at' => '2026-09-28',
            ],
            [
                'id' => 7,
                'employee_id' => 1052,
                'employee_full_id' => 'SMT-0103',
                'employee_name' => 'Rafiqul Islam',
                'amount' => 50000,
                'installment_count' => 10,
                'monthly_installment' => 5000,
                'applicable_month' => '2026-09',
                'purpose' => 'Children school admission and annual semester fee',
                'cash_value' => 25000,
                'bank_value' => 25000,
                'status' => 'Active',
                'applied_at' => '2026-08-14',
            ],
            [
                'id' => 8,
                'employee_id' => 1054,
                'employee_full_id' => 'SMT-0112',
                'employee_name' => 'Tahmina Akter',
                'amount' => 40000,
                'installment_count' => 8,
                'monthly_installment' => 5000,
                'applicable_month' => '2026-12',
                'purpose' => 'Professional ISTQB certification & exam voucher',
                'cash_value' => 10000,
                'bank_value' => 30000,
                'status' => 'Pending',
                'applied_at' => '2026-10-02',
            ],
            [
                'id' => 9,
                'employee_id' => 1056,
                'employee_full_id' => 'SMT-0120',
                'employee_name' => 'Mahbubur Rahman',
                'amount' => 120000,
                'installment_count' => 12,
                'monthly_installment' => 10000,
                'applicable_month' => '2026-01',
                'purpose' => 'Annual apartment rent advance payment',
                'cash_value' => 60000,
                'bank_value' => 60000,
                'status' => 'Repaid',
                'applied_at' => '2025-12-10',
            ],
            [
                'id' => 10,
                'employee_id' => 1058,
                'employee_full_id' => 'SMT-0135',
                'employee_name' => 'Shamima Nasrin',
                'amount' => 35000,
                'installment_count' => 7,
                'monthly_installment' => 5000,
                'applicable_month' => '2026-11',
                'purpose' => 'Durga Puja Festival Advance',
                'cash_value' => 15000,
                'bank_value' => 20000,
                'status' => 'Approved',
                'applied_at' => '2026-09-25',
            ],
        ];

        // Additional 25 loan records
        $loanPurposes = [
            'Provident Fund (PF) Non-Refundable Loan',
            'Motorcycle purchase loan for sales mobility',
            'Family medical emergency fund',
            'Marriage ceremony loan allowance',
            'Home electrical & AC installation advance',
            'Autumn festival bonus advance',
            'Laptop purchase scheme for remote office',
            'Mother eye surgery at Bangladesh Eye Hospital',
        ];
        $loanStatuses = ['Active', 'Approved', 'Pending', 'Repaid', 'Approved'];

        for ($j = 11; $j <= 35; $j++) {
            $e = $employeesData[$j % count($employeesData)];
            $amt = rand(4, 15) * 10000;
            $inst = rand(6, 15);
            $emi = round($amt / $inst);
            $loansData[] = [
                'id' => $j,
                'employee_id' => $e['employee_id'],
                'employee_full_id' => $e['employee_full_id'],
                'employee_name' => $e['name'],
                'amount' => $amt,
                'installment_count' => $inst,
                'monthly_installment' => $emi,
                'applicable_month' => '2026-' . str_pad((string) rand(8, 12), 2, '0', STR_PAD_LEFT),
                'purpose' => $loanPurposes[$j % count($loanPurposes)],
                'cash_value' => $amt * 0.3,
                'bank_value' => $amt * 0.7,
                'status' => $loanStatuses[$j % count($loanStatuses)],
                'applied_at' => '2026-0' . rand(6, 9) . '-' . rand(10, 28),
            ];
        }

        foreach ($loansData as $loan) {
            DB::table('hr_loans')->updateOrInsert(
                ['id' => $loan['id']],
                array_merge($loan, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        // 3. Seed 45+ Leave Applications (BLA 2006 Compliant)
        $leavesData = [
            [
                'id' => 1074,
                'employee_id' => 479,
                'employee_full_id' => 'SMT-0051',
                'employee_name' => 'Abdul Halim',
                'leave_type' => 'Casual Leave',
                'leave_type_id' => 1,
                'from_date' => '2026-10-15',
                'to_date' => '2026-10-17',
                'days_count' => 3,
                'reason' => 'Family emergency and personal affairs in hometown',
                'emergency_phone' => '01717186089',
                'status' => 'Pending Recommend',
                'applied_at' => '2026-10-02 11:30:00',
            ],
            [
                'id' => 1075,
                'employee_id' => 1047,
                'employee_full_id' => 'SMT-0026',
                'employee_name' => 'Nusrat Jahan',
                'leave_type' => 'Sick Leave',
                'leave_type_id' => 3,
                'from_date' => '2026-10-05',
                'to_date' => '2026-10-07',
                'days_count' => 3,
                'reason' => 'Severe viral fever and physician recommended bed rest',
                'emergency_phone' => '01823456789',
                'status' => 'Approved',
                'applied_at' => '2026-10-01 09:15:00',
                'recommended_by' => 'Ariful Islam (HR)',
                'approved_by' => 'Managing Director',
            ],
            [
                'id' => 1076,
                'employee_id' => 1048,
                'employee_full_id' => 'SMT-0042',
                'employee_name' => 'Tanvir Ahmed',
                'leave_type' => 'Earned Leave',
                'leave_type_id' => 2,
                'from_date' => '2026-09-20',
                'to_date' => '2026-09-24',
                'days_count' => 5,
                'reason' => 'Annual family vacation tour to Cox\'s Bazar',
                'emergency_phone' => '01934567890',
                'status' => 'Approved',
                'applied_at' => '2026-09-10 14:00:00',
                'recommended_by' => 'Head of Engineering',
                'approved_by' => 'HR Director',
            ],
            [
                'id' => 1077,
                'employee_id' => 1049,
                'employee_full_id' => 'SMT-0089',
                'employee_name' => 'Sadia Rahman',
                'leave_type' => 'Maternity Leave',
                'leave_type_id' => 4,
                'from_date' => '2026-09-01',
                'to_date' => '2026-12-21',
                'days_count' => 112,
                'reason' => 'Maternity leave as per Bangladesh Labour Act 2006 (16 weeks)',
                'emergency_phone' => '01645678901',
                'status' => 'Approved',
                'applied_at' => '2026-08-15 10:00:00',
                'recommended_by' => 'Head of Finance',
                'approved_by' => 'HR Board',
            ],
            [
                'id' => 1078,
                'employee_id' => 1050,
                'employee_full_id' => 'SMT-0104',
                'employee_name' => 'Kamrul Hasan',
                'leave_type' => 'Casual Leave',
                'leave_type_id' => 1,
                'from_date' => '2026-10-10',
                'to_date' => '2026-10-11',
                'days_count' => 2,
                'reason' => 'Attending cousin wedding ceremony in Munshiganj',
                'emergency_phone' => '01755667788',
                'status' => 'Pending 2nd Recommend',
                'applied_at' => '2026-10-02 16:45:00',
            ],
        ];

        $leaveTypes = [
            ['Casual Leave', 1, 2, 'Personal and urgent family business'],
            ['Sick Leave', 3, 3, 'Viral flu symptoms and doctor visit'],
            ['Earned Leave', 2, 4, 'Annual leave relaxation with family'],
            ['Compensatory Leave', 5, 1, 'In lieu of weekend duty on project rollout'],
            ['Short Leave', 6, 1, 'Urgent banking and passport renewal work'],
        ];

        for ($k = 1079; $k <= 1120; $k++) {
            $e = $employeesData[$k % count($employeesData)];
            $lt = $leaveTypes[$k % count($leaveTypes)];
            $day = rand(1, 25);
            $leavesData[] = [
                'id' => $k,
                'employee_id' => $e['employee_id'],
                'employee_full_id' => $e['employee_full_id'],
                'employee_name' => $e['name'],
                'leave_type' => $lt[0],
                'leave_type_id' => $lt[1],
                'from_date' => '2026-0' . rand(8, 9) . '-' . str_pad((string)$day, 2, '0', STR_PAD_LEFT),
                'to_date' => '2026-0' . rand(8, 9) . '-' . str_pad((string)min(28, $day + $lt[2]), 2, '0', STR_PAD_LEFT),
                'days_count' => $lt[2],
                'reason' => $lt[3],
                'emergency_phone' => $e['phone'],
                'status' => ['Approved', 'Approved', 'Pending Recommend', 'Approved', 'Pending 2nd Recommend'][rand(0, 4)],
                'applied_at' => '2026-0' . rand(8, 9) . '-01 10:00:00',
                'recommended_by' => 'Ariful Islam (HR)',
                'approved_by' => 'Managing Director',
            ];
        }

        foreach ($leavesData as $leave) {
            DB::table('leave_applications')->updateOrInsert(
                ['id' => $leave['id']],
                array_merge($leave, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        // 4. Seed 400+ Attendance Records across Multiple Employees (September & October 2026)
        $sampleEmps = array_slice($employeesData, 0, 10);
        $dates = [
            '2026-10-03' => ['in' => '09:05 AM', 'out' => '—', 'status' => 'Present', 'late' => 0],
            '2026-10-02' => ['in' => '—', 'out' => '—', 'status' => 'Weekend', 'late' => 0], // Friday BD
            '2026-10-01' => ['in' => '09:02 AM', 'out' => '06:05 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-30' => ['in' => '09:04 AM', 'out' => '06:12 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-29' => ['in' => '09:22 AM', 'out' => '06:30 PM', 'status' => 'Late', 'late' => 7],
            '2026-09-28' => ['in' => '08:58 AM', 'out' => '06:01 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-27' => ['in' => '09:01 AM', 'out' => '06:15 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-26' => ['in' => '—', 'out' => '—', 'status' => 'Weekend', 'late' => 0],
            '2026-09-25' => ['in' => '—', 'out' => '—', 'status' => 'Weekend', 'late' => 0], // Friday
            '2026-09-24' => ['in' => '09:12 AM', 'out' => '06:20 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-23' => ['in' => '09:00 AM', 'out' => '06:05 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-22' => ['in' => '09:28 AM', 'out' => '06:45 PM', 'status' => 'Late', 'late' => 13],
            '2026-09-21' => ['in' => '08:55 AM', 'out' => '06:10 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-20' => ['in' => '09:03 AM', 'out' => '06:02 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-19' => ['in' => '—', 'out' => '—', 'status' => 'Weekend', 'late' => 0],
            '2026-09-18' => ['in' => '—', 'out' => '—', 'status' => 'Weekend', 'late' => 0], // Friday
            '2026-09-17' => ['in' => '09:05 AM', 'out' => '06:00 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-16' => ['in' => '—', 'out' => '—', 'status' => 'Holiday', 'late' => 0], // Eid-e-Miladunnabi
            '2026-09-15' => ['in' => '09:01 AM', 'out' => '06:08 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-14' => ['in' => '09:35 AM', 'out' => '06:40 PM', 'status' => 'Late', 'late' => 20],
            '2026-09-13' => ['in' => '09:08 AM', 'out' => '06:15 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-12' => ['in' => '—', 'out' => '—', 'status' => 'Weekend', 'late' => 0],
            '2026-09-11' => ['in' => '—', 'out' => '—', 'status' => 'Weekend', 'late' => 0],
            '2026-09-10' => ['in' => '09:02 AM', 'out' => '06:05 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-09' => ['in' => '09:10 AM', 'out' => '06:20 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-08' => ['in' => '09:00 AM', 'out' => '06:00 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-07' => ['in' => '09:14 AM', 'out' => '06:12 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-06' => ['in' => '09:05 AM', 'out' => '06:08 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-05' => ['in' => '—', 'out' => '—', 'status' => 'Weekend', 'late' => 0],
            '2026-09-04' => ['in' => '—', 'out' => '—', 'status' => 'Weekend', 'late' => 0],
            '2026-09-03' => ['in' => '08:52 AM', 'out' => '06:01 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-02' => ['in' => '09:01 AM', 'out' => '06:10 PM', 'status' => 'Present', 'late' => 0],
            '2026-09-01' => ['in' => '09:05 AM', 'out' => '06:15 PM', 'status' => 'Present', 'late' => 0],
        ];

        $attRecordId = 1;
        foreach ($sampleEmps as $empIdx => $emp) {
            foreach ($dates as $dateStr => $attInfo) {
                // Slight variation per employee
                $status = $attInfo['status'];
                $late = $attInfo['late'];
                $inTime = $attInfo['in'];
                $outTime = $attInfo['out'];

                if ($status === 'Present' && $empIdx % 4 === 1 && $late === 0 && rand(1, 10) > 8) {
                    $status = 'Late';
                    $late = rand(5, 25);
                    $inTime = '09:' . str_pad((string)(15 + $late), 2, '0', STR_PAD_LEFT) . ' AM';
                }

                DB::table('attendance_records')->updateOrInsert(
                    ['id' => $attRecordId],
                    [
                        'employee_id' => $emp['employee_id'],
                        'employee_full_id' => $emp['employee_full_id'],
                        'employee_name' => $emp['name'],
                        'date' => $dateStr,
                        'in_time' => $inTime,
                        'out_time' => $outTime,
                        'status' => $status,
                        'working_hours' => $status === 'Present' || $status === 'Late' ? '9h 05m' : '—',
                        'overtime_hours' => '0h 00m',
                        'late_minutes' => $late,
                        'location' => 'Tejgaon I/A, Dhaka HQ',
                        'punch_source' => ['SilkBio-101TC', 'ZKTeco SpeedFace-V5L', 'Mobile Geo-Punch'][$empIdx % 3],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
                $attRecordId++;
            }
        }

        // 5. Seed 60+ Payslips (Multi-month salary records with BLA 2006 breakdown)
        $psId = 501;
        $payMonths = ['2026-09', '2026-08', '2026-07', '2026-06'];
        $payslipEmps = array_slice($employeesData, 0, 15);

        foreach ($payMonths as $m) {
            foreach ($payslipEmps as $emp) {
                $basic = $emp['basic_salary'];
                $house = $emp['house_rent'];
                $med = $emp['medical_allowance'];
                $conv = $emp['conveyance'];
                $gross = $emp['gross_salary'];
                $pf = $emp['pf_deduction'];
                $tax = $emp['tax_deduction'];
                $loan = ($emp['employee_id'] === 479 && $m === '2026-09') ? 10000 : 0;
                $totDeduct = $pf + $tax + $loan;
                $net = $gross - $totDeduct;

                DB::table('payslips')->updateOrInsert(
                    ['id' => $psId],
                    [
                        'employee_id' => $emp['employee_id'],
                        'employee_full_id' => $emp['employee_full_id'],
                        'employee_name' => $emp['name'],
                        'month' => $m,
                        'designation' => $emp['designation'],
                        'department' => $emp['department'],
                        'company' => 'Smart Technologies (BD) Ltd.',
                        'basic_salary' => $basic,
                        'house_rent' => $house,
                        'medical_allowance' => $med,
                        'conveyance' => $conv,
                        'special_allowance' => 0,
                        'total_earnings' => $gross,
                        'pf_deduction' => $pf,
                        'tax_deduction' => $tax,
                        'loan_deduction' => $loan,
                        'other_deductions' => 0,
                        'total_deductions' => $totDeduct,
                        'net_payable' => $net,
                        'payment_method' => 'Bank Transfer (EFTN / BEFTN)',
                        'payment_date' => $m . '-30',
                        'bank_name' => $emp['bank_name'],
                        'bank_account_no' => $emp['bank_account_no'],
                        'routing_no' => $emp['routing_name'],
                        'status' => 'Paid',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
                $psId++;
            }
        }

        // 6. Seed 12+ Official Notices & Gazetted Circulars
        $noticesData = [
            [
                'id' => 1,
                'title' => 'Gazetted Holiday: Durga Puja (বিজয়া দশমী) Observance',
                'description' => 'In accordance with the Ministry of Public Administration gazette, all corporate and factory operations will remain closed on Saturday, October 24, 2026 on the auspicious occasion of Vijaya Dashami.',
                'publish_at' => '2026-10-01',
                'expire_at' => '2026-10-25',
                'department' => 'All Departments',
                'priority' => 'High',
            ],
            [
                'id' => 2,
                'title' => 'Biometric Device Grace Period & IOM Excuse Policy Update',
                'description' => 'Employees are reminded that standard grace period is 15 minutes (until 09:15 AM). Late arrivals beyond 15 minutes must submit an electronic IOM (In-Out Movement) request with line manager recommendation.',
                'publish_at' => '2026-09-25',
                'expire_at' => '2026-12-31',
                'department' => 'Human Resources',
                'priority' => 'Normal',
            ],
            [
                'id' => 3,
                'title' => 'Festival Advance Loan Window Open for Autumn Quarter',
                'description' => 'Staff members eligible for company festival advances may apply through the HR Loans & Advances module. Repayment will be deducted in up to 6 monthly payroll installments.',
                'publish_at' => '2026-09-20',
                'expire_at' => '2026-10-31',
                'department' => 'Finance & Accounts',
                'priority' => 'Normal',
            ],
            [
                'id' => 4,
                'title' => 'Victory Day (বিজয় দিবস) Corporate Commemoration & Celebration',
                'description' => 'Smart Group will host a national flag hoisting and commemoration seminar on 16 December 2026 at corporate auditorium. All employees and their families are cordially invited.',
                'publish_at' => '2026-10-01',
                'expire_at' => '2026-12-17',
                'department' => 'Administration',
                'priority' => 'Normal',
            ],
            [
                'id' => 5,
                'title' => 'Annual Performance Appraisal 2026 Self-Evaluation Submission',
                'description' => 'All confirmed employees are requested to submit their annual self-evaluation scorecards on the HR Portal by November 15, 2026 for line manager review.',
                'publish_at' => '2026-10-02',
                'expire_at' => '2026-11-16',
                'department' => 'Human Resources',
                'priority' => 'High',
            ],
            [
                'id' => 6,
                'title' => 'Corporate Group Health & Hospitalization Insurance Benefit Expansion',
                'description' => 'Smart Group has renewed corporate health coverage with Pragati Life Insurance PLC. IPD coverage limit increased to ৳3,50,000 per annum with cashless admission at 65 network hospitals.',
                'publish_at' => '2026-09-15',
                'expire_at' => '2026-12-31',
                'department' => 'Human Resources',
                'priority' => 'Normal',
            ],
            [
                'id' => 7,
                'title' => 'Gazetted Holiday: Holy Eid-e-Miladunnabi (সাঃ) Observance',
                'description' => 'All offices, depots, and assembly units will remain closed on Wednesday, 16 September 2026 on the holy occasion of Eid-e-Miladunnabi (pbuh).',
                'publish_at' => '2026-09-12',
                'expire_at' => '2026-09-17',
                'department' => 'Administration',
                'priority' => 'High',
            ],
            [
                'id' => 8,
                'title' => 'Depot Stock Audit & Inventory Reconcile Schedule - Q3 2026',
                'description' => 'Annual Q3 physical inventory verification across Dhaka North, Dhaka South, and Chittagong depots will take place from October 10 to October 12, 2026.',
                'publish_at' => '2026-10-01',
                'expire_at' => '2026-10-13',
                'department' => 'Supply Chain',
                'priority' => 'Normal',
            ],
        ];

        foreach ($noticesData as $notice) {
            DB::table('notices')->updateOrInsert(
                ['id' => $notice['id']],
                array_merge($notice, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        // 7. Seed 30+ SND Retail Customers / Dealers
        $customers = [
            ['id' => 1, 'name' => 'Bismillah Store & Electronics', 'proprietor' => 'Haji Md. Younus', 'code' => 'CUST-001', 'route' => 'Route 1 - Dhanmondi Mirpur Road', 'territory' => 'Dhaka South - Zone 2', 'phone' => '01711223344', 'due_balance' => 45000, 'credit_limit' => 150000],
            ['id' => 2, 'name' => 'Popular Telecom & Gadget Hub', 'proprietor' => 'Sharif Uddin', 'code' => 'CUST-002', 'route' => 'Route 2 - Gulshan Banani Commercial', 'territory' => 'Dhaka North - Zone 1', 'phone' => '01822334455', 'due_balance' => 12000, 'credit_limit' => 200000],
            ['id' => 3, 'name' => 'Rahman Brothers Electric & Appliance', 'proprietor' => 'Abdur Rahman', 'code' => 'CUST-003', 'route' => 'Route 3 - Motijheel Dilkusha Loop', 'territory' => 'Dhaka Central - Zone 3', 'phone' => '01933445566', 'due_balance' => 88000, 'credit_limit' => 120000],
            ['id' => 4, 'name' => 'Chittagong Digital Mart', 'proprietor' => 'Morshedul Alam', 'code' => 'CUST-004', 'route' => 'Route 4 - Agrabad Commercial Loop', 'territory' => 'Chittagong Port Zone', 'phone' => '01644556677', 'due_balance' => 35000, 'credit_limit' => 250000],
            ['id' => 5, 'name' => 'Uttara Modern Gadgets', 'proprietor' => 'Zakir Hossain', 'code' => 'CUST-005', 'route' => 'Route 5 - Sector 3 & 7 Loop', 'territory' => 'Dhaka North - Zone 1', 'phone' => '01755667788', 'due_balance' => 0, 'credit_limit' => 100000],
            ['id' => 6, 'name' => 'Sylhet Metro Electronics', 'proprietor' => 'Kamal Uddin', 'code' => 'CUST-006', 'route' => 'Route 6 - Zindabazar Shopping Corridor', 'territory' => 'Sylhet Central', 'phone' => '01866778899', 'due_balance' => 22000, 'credit_limit' => 180000],
            ['id' => 7, 'name' => 'Padma Lubricants & Auto Center', 'proprietor' => 'Haji Sirajul Islam', 'code' => 'CUST-007', 'route' => 'Route 7 - Mirpur 10 Circle & Pallabi', 'territory' => 'Dhaka North - Zone 2', 'phone' => '01898765432', 'due_balance' => 12500, 'credit_limit' => 150000],
            ['id' => 8, 'name' => 'Karnaphuli Trading Agency', 'proprietor' => 'Rashidul Haque', 'code' => 'CUST-008', 'route' => 'Route 8 - Agrabad Commercial Area', 'territory' => 'Chittagong Central', 'phone' => '01912445566', 'due_balance' => 62000, 'credit_limit' => 220000],
            ['id' => 9, 'name' => 'CodeTap Distributors Ltd.', 'proprietor' => 'Md. Kabirul Islam', 'code' => 'CUST-009', 'route' => 'Route 9 - Tejgaon Industrial Belt', 'territory' => 'Dhaka Central', 'phone' => '01712345678', 'due_balance' => 0, 'credit_limit' => 300000],
            ['id' => 10, 'name' => 'Jamuna Auto & Spares', 'proprietor' => 'Enayet Ullah', 'code' => 'CUST-010', 'route' => 'Route 10 - Bogura Highway Hub', 'territory' => 'Rajshahi Zone', 'phone' => '01711998811', 'due_balance' => 18000, 'credit_limit' => 120000],
            ['id' => 11, 'name' => 'Meghna Trade International', 'proprietor' => 'Sajjad Hossain', 'code' => 'CUST-011', 'route' => 'Route 11 - Narayanganj Sadar Port', 'territory' => 'Narayanganj Zone', 'phone' => '01822114455', 'due_balance' => 41000, 'credit_limit' => 160000],
            ['id' => 12, 'name' => 'Surma Engineering & Supplies', 'proprietor' => 'Delwar Hossain', 'code' => 'CUST-012', 'route' => 'Route 12 - Amberkhana Point', 'territory' => 'Sylhet Central', 'phone' => '01911445566', 'due_balance' => 9500, 'credit_limit' => 140000],
            ['id' => 13, 'name' => 'Rupsha Motor Oil Emporium', 'proprietor' => 'Shamsul Alam', 'code' => 'CUST-013', 'route' => 'Route 13 - Daulatpur Industrial Area', 'territory' => 'Khulna Zone', 'phone' => '01744551122', 'due_balance' => 28000, 'credit_limit' => 130000],
            ['id' => 14, 'name' => 'Barendra Machinery & Lubes', 'proprietor' => 'Mostafizur Rahman', 'code' => 'CUST-014', 'route' => 'Route 14 - Shaheb Bazar Commercial', 'territory' => 'Rajshahi Metro', 'phone' => '01733221100', 'due_balance' => 15000, 'credit_limit' => 110000],
            ['id' => 15, 'name' => 'Gazipur Industrial Power Lubes', 'proprietor' => 'Kaiser Ahmed', 'code' => 'CUST-015', 'route' => 'Route 15 - Joydebpur Chowrasta', 'territory' => 'Gazipur Zone', 'phone' => '01811990022', 'due_balance' => 54000, 'credit_limit' => 200000],
        ];

        for ($cIdx = 16; $cIdx <= 30; $cIdx++) {
            $customers[] = [
                'id' => $cIdx,
                'name' => 'Retail Dealer #' . $cIdx . ' ' . ['Trading', 'Motors', 'Enterprise', 'Lubes', 'Automotive'][$cIdx % 5],
                'proprietor' => 'Md. Proprietor ' . $cIdx,
                'code' => 'CUST-' . str_pad((string)$cIdx, 3, '0', STR_PAD_LEFT),
                'route' => 'Route ' . $cIdx . ' - Regional Business Center',
                'territory' => ['Dhaka North', 'Dhaka South', 'Chittagong Metro', 'Gazipur', 'Narayanganj'][$cIdx % 5],
                'phone' => '017' . rand(10000000, 99999999),
                'due_balance' => rand(0, 50) * 1000,
                'credit_limit' => rand(10, 25) * 10000,
            ];
        }

        foreach ($customers as $c) {
            DB::table('snd_customers')->updateOrInsert(
                ['id' => $c['id']],
                array_merge($c, ['status' => 'Active', 'created_at' => $now, 'updated_at' => $now])
            );
        }

        // 8. Seed 35+ SND Sales Orders
        for ($ordId = 1; $ordId <= 35; $ordId++) {
            $cust = $customers[$ordId % count($customers)];
            $sub = rand(25, 250) * 1000;
            $disc = round($sub * 0.05);
            $pay = $sub - $disc;
            $date = '2026-0' . rand(8, 9) . '-' . str_pad((string)rand(1, 28), 2, '0', STR_PAD_LEFT);
            if ($ordId <= 5) {
                $date = '2026-10-0' . min(3, $ordId);
            }

            DB::table('snd_orders')->updateOrInsert(
                ['id' => $ordId],
                [
                    'order_no' => 'SO-DPT-' . (1040 + $ordId),
                    'customer_id' => $cust['id'],
                    'customer_name' => $cust['name'],
                    'date' => $date,
                    'item_count' => rand(2, 6),
                    'total_amount' => $sub,
                    'discount_amount' => $disc,
                    'payable_amount' => $pay,
                    'payment_status' => $ordId % 3 === 0 ? 'Paid' : 'Pending',
                    'delivery_status' => $ordId % 3 === 0 ? 'Delivered' : ($ordId % 2 === 0 ? 'Dispatched' : 'Processing'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 9. Seed 40+ SND Outlet Visits
        $outcomes = ['Order Placed', 'Order Placed', 'Payment Collected', 'Follow Up Required', 'Stock Verified'];
        for ($vId = 1; $vId <= 40; $vId++) {
            $cust = $customers[$vId % count($customers)];
            $vDate = '2026-10-0' . min(3, ($vId % 3) + 1);
            if ($vId > 15) {
                $vDate = '2026-09-' . str_pad((string)rand(15, 30), 2, '0', STR_PAD_LEFT);
            }

            DB::table('snd_visits')->updateOrInsert(
                ['id' => $vId],
                [
                    'customer_id' => $cust['id'],
                    'customer_name' => $cust['name'],
                    'date' => $vDate,
                    'time' => str_pad((string)rand(9, 17), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)rand(10, 50), 2, '0', STR_PAD_LEFT),
                    'purpose' => 'Regular sales beat routine and retailer replenishment audit',
                    'outcome' => $outcomes[$vId % count($outcomes)],
                    'note' => 'Retailer inquired about festival trade schemes and delivery schedules',
                    'representative' => 'Abdul Halim (SMT-0051)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 10. Seed 20+ SFM Commitments
        $commitments = [
            ['id' => 1, 'employee_id' => 479, 'employee_name' => 'Abdul Halim', 'code' => 'SMT-0051', 'territory' => 'Dhaka North - Zone 1', 'month' => '2026-10', 'target_value' => 2500000, 'commitment_value' => 2677000, 'actual_sales' => 2180000, 'pcmo_commitment' => 1275000, 'hddo_commitment' => 1402000],
            ['id' => 2, 'employee_id' => 1050, 'employee_name' => 'Kamrul Hasan', 'code' => 'SMT-0104', 'territory' => 'Dhaka South - Tejgaon Zone', 'month' => '2026-10', 'target_value' => 2200000, 'commitment_value' => 2200000, 'actual_sales' => 1950000, 'pcmo_commitment' => 1000000, 'hddo_commitment' => 1200000],
            ['id' => 3, 'employee_id' => 1051, 'employee_name' => 'Zubair Hossain', 'code' => 'SMT-0105', 'territory' => 'Chittagong Metro & Port', 'month' => '2026-10', 'target_value' => 3000000, 'commitment_value' => 2950000, 'actual_sales' => 2850000, 'pcmo_commitment' => 1400000, 'hddo_commitment' => 1550000],
            ['id' => 4, 'employee_id' => 1057, 'employee_name' => 'Tariqul Alam', 'code' => 'SMT-0128', 'territory' => 'Chittagong - Agrabad Loop', 'month' => '2026-10', 'target_value' => 1800000, 'commitment_value' => 1750000, 'actual_sales' => 1420000, 'pcmo_commitment' => 850000, 'hddo_commitment' => 900000],
            ['id' => 5, 'employee_id' => 1061, 'employee_name' => 'Anisur Rahman', 'code' => 'SMT-0145', 'territory' => 'Bogura & Northern Belt', 'month' => '2026-10', 'target_value' => 1600000, 'commitment_value' => 1550000, 'actual_sales' => 1310000, 'pcmo_commitment' => 700000, 'hddo_commitment' => 850000],
        ];

        for ($cmId = 6; $cmId <= 20; $cmId++) {
            $e = $employeesData[$cmId % count($employeesData)];
            $tVal = rand(12, 30) * 100000;
            $cVal = round($tVal * rand(95, 108) / 100);
            $aVal = round($cVal * rand(70, 92) / 100);
            $commitments[] = [
                'id' => $cmId,
                'employee_id' => $e['employee_id'],
                'employee_name' => $e['name'],
                'code' => $e['employee_full_id'],
                'territory' => 'Territory Zone #' . $cmId,
                'month' => '2026-10',
                'target_value' => $tVal,
                'commitment_value' => $cVal,
                'actual_sales' => $aVal,
                'pcmo_commitment' => round($cVal * 0.45),
                'hddo_commitment' => round($cVal * 0.55),
            ];
        }

        foreach ($commitments as $cm) {
            DB::table('sfm_commitments')->updateOrInsert(
                ['id' => $cm['id']],
                array_merge($cm, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        // 11. Seed 25+ Tour Plans & Tour Claims
        for ($tpId = 1; $tpId <= 15; $tpId++) {
            $e = $employeesData[$tpId % count($employeesData)];
            DB::table('tour_plans')->updateOrInsert(
                ['id' => $tpId],
                [
                    'month' => '2026-10',
                    'employee_id' => $e['employee_id'],
                    'employee_name' => $e['name'],
                    'status' => $tpId % 3 === 0 ? 'Pending Approval' : 'Approved',
                    'total_working_days' => 24,
                    'tour_days' => rand(10, 20),
                    'base_station' => ['Mirpur Central Depot', 'Tejgaon Industrial Depot', 'Agrabad Depot', 'Bogura Hub'][$tpId % 4],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $routes = [
            'Dhaka ➔ Chittagong Regional Depot',
            'Dhaka ➔ Sylhet Distribution Center',
            'Dhaka ➔ Bogra Northern Hub',
            'Chittagong ➔ Cox\'s Bazar Retail Inspection',
            'Dhaka ➔ Gazipur Industrial Plant',
            'Dhaka ➔ Narayanganj Port Beat',
            'Dhaka ➔ Mymensingh Commercial Center',
        ];

        for ($clId = 1; $clId <= 25; $clId++) {
            $e = $employeesData[$clId % count($employeesData)];
            $da = rand(400, 900);
            $ta = rand(350, 2800);
            $hotel = rand(0, 1) ? rand(1000, 2500) : 0;
            $other = rand(0, 500);
            $tot = $da + $ta + $hotel + $other;

            DB::table('tour_claims')->updateOrInsert(
                ['id' => $clId],
                [
                    'claim_no' => 'CLM-2026-' . str_pad((string)(240 + $clId), 3, '0', STR_PAD_LEFT),
                    'month' => '2026-10',
                    'date' => '2026-10-0' . min(3, ($clId % 3) + 1),
                    'employee_id' => $e['employee_id'],
                    'employee_name' => $e['name'],
                    'route' => $routes[$clId % count($routes)],
                    'da_amount' => $da,
                    'ta_amount' => $ta,
                    'hotel_amount' => $hotel,
                    'other_amount' => $other,
                    'total_amount' => $tot,
                    'status' => $clId % 4 === 0 ? 'Paid' : ($clId % 3 === 0 ? 'Submitted' : 'Approved'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 12. Seed 20+ Short Leaves
        for ($slId = 1; $slId <= 20; $slId++) {
            $e = $employeesData[$slId % count($employeesData)];
            DB::table('short_leaves')->updateOrInsert(
                ['id' => $slId],
                [
                    'employee_id' => $e['employee_id'],
                    'employee_name' => $e['name'],
                    'leave_day' => '2026-10-' . str_pad((string)rand(1, 20), 2, '0', STR_PAD_LEFT),
                    'leave_type' => $slId % 2 === 0 ? 'early' : 'delay',
                    'early_out_time' => $slId % 2 === 0 ? '03:30 PM' : null,
                    'delay_in_time' => $slId % 2 === 1 ? '10:30 AM' : null,
                    'reason' => ['Doctor consultation appointment', 'Urgent family bank transaction', 'Child school parent meeting', 'Passport biometric appointment'][$slId % 4],
                    'emergency_phone' => $e['phone'],
                    'status' => $slId % 3 === 0 ? 'Approved' : 'Pending Recommend',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 13. Seed 20+ Late Requests (IOM)
        $iomReasons = [
            'Severe traffic gridlock on Airport Highway near Kuril flyover',
            'Dhaka Metro Rail technical stoppage between Mirpur 10 and Agargaon',
            'Waterlogging after heavy monsoon rain on Mirpur Road',
            'Emergency client breakdown consultation prior to office arrival',
            'Public transit bus breakdown near Mohakhali inter-district terminal',
        ];

        for ($lrId = 1; $lrId <= 20; $lrId++) {
            $e = $employeesData[$lrId % count($employeesData)];
            DB::table('late_requests')->updateOrInsert(
                ['id' => $lrId],
                [
                    'employee_id' => $e['employee_id'],
                    'employee_name' => $e['name'],
                    'iom_type' => ['Traffic Congestion', 'Transit Delay', 'Client Emergency', 'Weather Disruption'][$lrId % 4],
                    'date' => '2026-09-' . str_pad((string)rand(10, 30), 2, '0', STR_PAD_LEFT),
                    'in_time' => '09:' . rand(22, 45) . ':00',
                    'out_time' => '18:15:00',
                    'purpose' => $iomReasons[$lrId % count($iomReasons)],
                    'status' => $lrId % 3 === 0 ? 'Approved' : 'Pending',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 14. Seed 15+ Shift Exchanges
        for ($seId = 1; $seId <= 15; $seId++) {
            $e = $employeesData[$seId % count($employeesData)];
            DB::table('shift_exchanges')->updateOrInsert(
                ['id' => $seId],
                [
                    'employee_id' => $e['employee_id'],
                    'employee_name' => $e['name'],
                    'current_shift' => 'Morning Shift (08:00 - 17:00)',
                    'target_shift' => 'General Shift (09:00 - 18:00)',
                    'exchange_date' => '2026-10-' . str_pad((string)rand(10, 25), 2, '0', STR_PAD_LEFT),
                    'description' => 'Morning academic exam and study schedule',
                    'status' => $seId % 2 === 0 ? 'Approved' : 'Pending',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 15. Seed 15+ Outwork Applications
        for ($owId = 1; $owId <= 15; $owId++) {
            $e = $employeesData[$owId % count($employeesData)];
            DB::table('outwork_applications')->updateOrInsert(
                ['id' => $owId],
                [
                    'employee_id' => $e['employee_id'],
                    'date' => '2026-10-0' . min(3, ($owId % 3) + 1),
                    'start_time' => '10:00 AM',
                    'return_time' => '04:30 PM',
                    'not_return' => false,
                    'note' => 'Client enterprise architecture review and server room audit at customer office',
                    'status' => $owId % 2 === 0 ? 'Approved' : 'Pending Recommend',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
