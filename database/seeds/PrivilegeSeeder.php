<?php

use Illuminate\Database\Seeder;

class PrivilegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('privileges')->insert([

            // 学生端
            [
                'name' => 'sign_up_contest',
                'display_name' => '报名参赛',
                'description' => '通过学生报名入口报名参加比赛'
            ],
            // 校管理员端
            [
                'name' => 'manage_school_teams',
                'display_name' => '管理本校参赛情况',
                'description' => '管理本校所有的参赛队伍'
            ],
            [
                'name' => 'view_school_results',
                'display_name' => '查看本校获奖结果',
                'description' => '查看本校的比赛结果以及审核情况'
            ],
            [
                'name' => 'sign_up_contest',
                'display_name' => '报名参赛',
                'description' => '通过学生报名入口报名参加比赛'
            ],
            [
                'name' => 'recommend_experts',
                'display_name' => '推荐专家',
                'description' => '填写相关信息向评审会推荐本校的专家'
            ],
            // 系统管理员端
            [
                'name' => 'manage_schools',
                'display_name' => '管理学校列表',
                'description' => '管理系统中的学校列表'
            ],
            [
                'name' => 'manage_contest',
                'display_name' => '管理竞赛',
                'description' => '删除、添加、修改系统中的竞赛信息'
            ],
            [
                'name' => 'manage_problems',
                'display_name' => '管理题目',
                'description' => '删除、添加、修改竞赛的题目及相关信息'
            ],
            [
                'name' => 'manage_school_admins',
                'display_name' => '管理校管理员',
                'description' => '生成、删除、修改对应学校的管理员账号'
            ],
            [
                'name' => 'manage_all_teams',
                'display_name' => '管理所有参赛队伍',
                'description' => '管理所有'
            ],
            [
                'name' => 'examine_experts',
                'display_name' => '审核专家',
                'description' => '审核学校管理员推荐的专家'
            ],
        ]);
    }
}
