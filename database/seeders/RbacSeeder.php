<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    private const GUARD = 'web';

    /**
     * @var array<int, string>
     */
    private const SUBJECTS = [
        'User',
        'Role',
        'Employee',
        'Position',
        'Department',
        'EmployeeAttendance',
        'Item',
        'Location',
        'AssetLoan',
        'InventoryLedger',
        'MonthlySchedule',
        'Abnormality',
        'Zone',
        'Workstation',
        'InspectionCriteria',
        'MasterWorkstationCriteria',
        'FiveREvaluation',
        'TrendAbnormality',
        'Bulletin',
        'QuickLink',
        'GeneralDocument',
    ];

    /**
     * @var array<int, string>
     */
    private const STANDARD_ACTIONS = [
        'ViewAny',
        'View',
        'Create',
        'Update',
        'Delete',
        'DeleteAny',
        'Restore',
        'RestoreAny',
        'ForceDelete',
        'ForceDeleteAny',
        'Replicate',
        'Reorder',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissionNames = $this->buildAllPermissionNames();

        foreach ($allPermissionNames as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => self::GUARD,
            ]);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => self::GUARD]);
        $managerialRole = Role::firstOrCreate(['name' => 'managerial_staff', 'guard_name' => self::GUARD]);
        $staffPenyeliaRole = Role::firstOrCreate(['name' => 'staff_penyelia', 'guard_name' => self::GUARD]);
        $pelaksanaRole = Role::firstOrCreate(['name' => 'pelaksana_5r', 'guard_name' => self::GUARD]);

        $superAdminRole->syncPermissions($allPermissionNames);
        $managerialRole->syncPermissions($this->getManagerialStaffPermissions());
        $staffPenyeliaRole->syncPermissions($this->getStaffPenyeliaPermissions());
        $pelaksanaRole->syncPermissions($this->getPelaksana5RPermissions());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return array<int, string>
     */
    private function buildAllPermissionNames(): array
    {
        $permissions = [];

        foreach (self::SUBJECTS as $subject) {
            foreach (self::STANDARD_ACTIONS as $action) {
                $permissions[] = "{$action}:{$subject}";
            }
        }

        return $permissions;
    }

    /**
     * @return array<int, string>
     */
    private function getManagerialStaffPermissions(): array
    {
        return [
            'ViewAny:Employee',
            'View:Employee',
            'ViewAny:Position',
            'View:Position',
            'ViewAny:Department',
            'View:Department',
            'ViewAny:EmployeeAttendance',
            'View:EmployeeAttendance',
            'ViewAny:Item',
            'View:Item',
            'ViewAny:Location',
            'View:Location',
            'ViewAny:AssetLoan',
            'View:AssetLoan',
            'ViewAny:InventoryLedger',
            'View:InventoryLedger',
            'ViewAny:MonthlySchedule',
            'View:MonthlySchedule',
            'ViewAny:Abnormality',
            'View:Abnormality',
            'Update:Abnormality',
            'ViewAny:Zone',
            'View:Zone',
            'ViewAny:Workstation',
            'View:Workstation',
            'ViewAny:InspectionCriteria',
            'View:InspectionCriteria',
            'ViewAny:MasterWorkstationCriteria',
            'View:MasterWorkstationCriteria',
            'ViewAny:FiveREvaluation',
            'View:FiveREvaluation',
            'Create:FiveREvaluation',
            'Update:FiveREvaluation',
            'ViewAny:TrendAbnormality',
            'View:TrendAbnormality',
            'ViewAny:Bulletin',
            'View:Bulletin',
            'Create:Bulletin',
            'Update:Bulletin',
            'ViewAny:QuickLink',
            'View:QuickLink',
            'Create:QuickLink',
            'Update:QuickLink',
            'ViewAny:GeneralDocument',
            'View:GeneralDocument',
            'Create:GeneralDocument',
            'Update:GeneralDocument',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function getStaffPenyeliaPermissions(): array
    {
        return [
            'ViewAny:Employee',
            'View:Employee',
            'ViewAny:Position',
            'View:Position',
            'ViewAny:Department',
            'View:Department',
            'ViewAny:EmployeeAttendance',
            'View:EmployeeAttendance',
            'ViewAny:Item',
            'View:Item',
            'ViewAny:Location',
            'View:Location',
            'ViewAny:MonthlySchedule',
            'View:MonthlySchedule',
            'ViewAny:Abnormality',
            'View:Abnormality',
            'Update:Abnormality',
            'ViewAny:Zone',
            'View:Zone',
            'ViewAny:Workstation',
            'View:Workstation',
            'ViewAny:InspectionCriteria',
            'View:InspectionCriteria',
            'ViewAny:MasterWorkstationCriteria',
            'View:MasterWorkstationCriteria',
            'ViewAny:FiveREvaluation',
            'View:FiveREvaluation',
            'Create:FiveREvaluation',
            'Update:FiveREvaluation',
            'ViewAny:TrendAbnormality',
            'View:TrendAbnormality',
            'ViewAny:Bulletin',
            'View:Bulletin',
            'ViewAny:QuickLink',
            'View:QuickLink',
            'ViewAny:GeneralDocument',
            'View:GeneralDocument',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function getPelaksana5RPermissions(): array
    {
        return [
            'ViewAny:MonthlySchedule',
            'View:MonthlySchedule',
            'ViewAny:Abnormality',
            'View:Abnormality',
            'Create:Abnormality',
            'Update:Abnormality',
            'ViewAny:Zone',
            'View:Zone',
            'ViewAny:Workstation',
            'View:Workstation',
            'ViewAny:InspectionCriteria',
            'View:InspectionCriteria',
            'ViewAny:MasterWorkstationCriteria',
            'View:MasterWorkstationCriteria',
            'ViewAny:FiveREvaluation',
            'View:FiveREvaluation',
            'ViewAny:TrendAbnormality',
            'View:TrendAbnormality',
            'ViewAny:Bulletin',
            'View:Bulletin',
            'ViewAny:QuickLink',
            'View:QuickLink',
            'ViewAny:GeneralDocument',
            'View:GeneralDocument',
        ];
    }
}
