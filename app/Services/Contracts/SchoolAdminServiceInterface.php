<?php

/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/12
 * Time: 下午3:24
 */

namespace App\Services\Contracts;

interface SchoolAdminServiceInterface
{
    function login(string $loginName, string $password, string $ip, string $client);

    function getStartedContest();

    function addSchoolTeam(array $schoolTeamInfo): bool;

    function getSchoolTeams(int $schoolId, int $contestId, int $page, int $size);

    function updateSchoolTeam(int $schoolTeamId, array $schoolTeamInfo): bool;

    function deleteSchoolTeam(int $schoolTeamId): bool;

    function checkSchoolTeam(int $schoolTeamId): bool;

    function getSchoolResults(int $schoolId, int $contestId, int $page, int $size);

    function exportSchoolTeams(int $schoolId, int $contestId);

    function exportSchoolResults(int $schoolId, int $contestId);
}

?>