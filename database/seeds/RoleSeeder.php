<?php

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        // 创建三个角色
        DB::table('roles')->insert([
            [
                'name' => 'student',
                'display_name' => '学生'
            ],
            [
                'name' => 'school_admin',
                'display_name' => '学校管理员'
            ],
            [
                'name' => 'system_admin',
                'display_name' => '系统管理员'
            ]
        ]);

        // 设定权限

        $studentPrivileges = ['sign_up_contest'];
        $schoolAdminPrivileges = ['manage_school_teams','view_school_results','sign_up_contest','recommend_experts'];
        $systemAdminPrivileges = [
            'manage_schools','manage_contest','manage_problems',
            'manage_school_admins','manage_all_teams','examine_experts',
            'manage_privilege','manage_users','manage_news','manage_notices','manage_files'
        ];

        $privileges = [];

        foreach ($studentPrivileges as $privilege) {
            $privileges[] =[
                'role_name' => 'student',
                'privilege_name' => $privilege
            ];
        }

        foreach ($schoolAdminPrivileges as $privilege) {
            $privileges[] =[
                'role_name' => 'school_admin',
                'privilege_name' => $privilege
            ];
        }

        foreach ($systemAdminPrivileges as $privilege) {
            $privileges[] =[
                'role_name' => 'system_admin',
                'privilege_name' => $privilege
            ];
        }

        DB::table('role_privileges')->insert($privileges);
    }
}
