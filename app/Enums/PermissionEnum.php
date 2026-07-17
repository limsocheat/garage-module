<?php

namespace Modules\Garage\Enums;

enum PermissionEnum: string
{
    case VIEW_ANY_GARAGE_VEHICLE = 'VIEW_ANY_GARAGE_VEHICLE';
    case VIEW_GARAGE_VEHICLE = 'VIEW_GARAGE_VEHICLE';
    case CREATE_GARAGE_VEHICLE = 'CREATE_GARAGE_VEHICLE';
    case UPDATE_GARAGE_VEHICLE = 'UPDATE_GARAGE_VEHICLE';
    case DELETE_GARAGE_VEHICLE = 'DELETE_GARAGE_VEHICLE';
    case RESTORE_GARAGE_VEHICLE = 'RESTORE_GARAGE_VEHICLE';
    case FORCE_DELETE_GARAGE_VEHICLE = 'FORCE_DELETE_GARAGE_VEHICLE';

    // Service jobs are captured by the Garage app; the dashboard is read-only
    // (owners review the job + photo proof + settlement status).
    case VIEW_ANY_GARAGE_SERVICE_JOB = 'VIEW_ANY_GARAGE_SERVICE_JOB';
    case VIEW_GARAGE_SERVICE_JOB = 'VIEW_GARAGE_SERVICE_JOB';
}
