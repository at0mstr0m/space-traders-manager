<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum MountSymbols: string
{
    use EnumUtils;

    case MOUNT_GAS_SIPHON_I = 'MOUNT_GAS_SIPHON_I';
    case MOUNT_GAS_SIPHON_II = 'MOUNT_GAS_SIPHON_II';
    case MOUNT_GAS_SIPHON_III = 'MOUNT_GAS_SIPHON_III';
    case MOUNT_SURVEYOR_I = 'MOUNT_SURVEYOR_I';
    case MOUNT_SURVEYOR_II = 'MOUNT_SURVEYOR_II';
    case MOUNT_SURVEYOR_III = 'MOUNT_SURVEYOR_III';
    case MOUNT_SENSOR_ARRAY_I = 'MOUNT_SENSOR_ARRAY_I';
    case MOUNT_SENSOR_ARRAY_II = 'MOUNT_SENSOR_ARRAY_II';
    case MOUNT_SENSOR_ARRAY_III = 'MOUNT_SENSOR_ARRAY_III';
    case MOUNT_MINING_LASER_I = 'MOUNT_MINING_LASER_I';
    case MOUNT_MINING_LASER_II = 'MOUNT_MINING_LASER_II';
    case MOUNT_MINING_LASER_III = 'MOUNT_MINING_LASER_III';
    case MOUNT_LASER_CANNON_I = 'MOUNT_LASER_CANNON_I';
    case MOUNT_MISSILE_LAUNCHER_I = 'MOUNT_MISSILE_LAUNCHER_I';
    case MOUNT_TURRET_I = 'MOUNT_TURRET_I';

    public static function miningLasers(): array
    {
        return [
            self::MOUNT_MINING_LASER_I,
            self::MOUNT_MINING_LASER_II,
            self::MOUNT_MINING_LASER_III,
        ];
    }

    public static function surveyors(): array
    {
        return [
            self::MOUNT_SURVEYOR_I,
            self::MOUNT_SURVEYOR_II,
            self::MOUNT_SURVEYOR_III,
        ];
    }

    public static function sensorArrays(): array
    {
        return [
            self::MOUNT_SENSOR_ARRAY_I,
            self::MOUNT_SENSOR_ARRAY_II,
            self::MOUNT_SENSOR_ARRAY_III,
        ];
    }

    public static function gasSiphons(): array
    {
        return [
            self::MOUNT_GAS_SIPHON_I,
            self::MOUNT_GAS_SIPHON_II,
            self::MOUNT_GAS_SIPHON_III,
        ];
    }

    public static function laserCannons(): array
    {
        return [
            self::MOUNT_LASER_CANNON_I,
        ];
    }

    public static function missileLaunchers(): array
    {
        return [
            self::MOUNT_MISSILE_LAUNCHER_I,
        ];
    }

    public static function turrets(): array
    {
        return [
            self::MOUNT_TURRET_I,
        ];
    }

    public function isMiningLaser(): bool
    {
        return in_array($this->value, self::miningLasers());
    }

    public function isSurveyor(): bool
    {
        return in_array($this->value, self::surveyors());
    }

    public function isSensorArray(): bool
    {
        return in_array($this->value, self::sensorArrays());
    }

    public function isGasSiphon(): bool
    {
        return in_array($this->value, self::gasSiphons());
    }

    public function isLaserCannon(): bool
    {
        return in_array($this->value, self::laserCannons());
    }

    public function isMissileLauncher(): bool
    {
        return in_array($this->value, self::missileLaunchers());
    }

    public function isTurret(): bool
    {
        return in_array($this->value, self::turrets());
    }

    public function isWeapon(): bool
    {
        return $this->isLaserCannon()
            || $this->isMissileLauncher()
            || $this->isTurret();
    }

    public function isUtility(): bool
    {
        return $this->isMiningLaser()
            || $this->isSurveyor()
            || $this->isSensorArray()
            || $this->isGasSiphon();
    }
}
