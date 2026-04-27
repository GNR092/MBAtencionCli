<?php

namespace Canva\HBD\Traits;

use Carbon\Carbon;

trait HasHbdBirthday
{
    public function getDiasParaCumpleAttribute(): int
    {
        $hoy = now()->startOfDay();
        $cumple = $this->getBirthdayDateForYear($hoy->year);

        if ($cumple->lt($hoy)) {
            $cumple = $this->getBirthdayDateForYear($hoy->year + 1);
        }

        return (int) $hoy->diffInDays($cumple->startOfDay());
    }

    public function getEdadAttribute(): int
    {
        $birthday = $this->getBirthdayField();
        if (!$birthday) {
            return 0;
        }

        return (int) now()->year - (int) Carbon::parse($birthday)->year;
    }

    public function getProximoCumpleAttribute(): Carbon
    {
        $hoy = now()->startOfDay();
        return $this->getBirthdayDateForYear($hoy->year);
    }

    public function getEsCumpleHoyAttribute(): bool
    {
        return $this->getBirthdayDateForYear(now()->year)->isToday();
    }

    public function getIsBirthdayThisMonthAttribute(): bool
    {
        return $this->getBirthdayDateForYear(now()->year)->month === now()->month;
    }

    public function getFechaNacimientoFormattedAttribute(): ?string
    {
        $birthday = $this->getBirthdayField();
        return $birthday ? Carbon::parse($birthday)->isoFormat('D [de] MMMM') : null;
    }

    private function getBirthdayField()
    {
        $field = config('hbd.birthday_field', 'fecha_nacimiento');
        return $this->{$field} ?? null;
    }

    private function getBirthdayDateForYear(int $year): Carbon
    {
        $birthday = $this->getBirthdayField();
        if (!$birthday) {
            return now()->setYear($year)->startOfYear();
        }

        $parsed = Carbon::parse($birthday);
        return $parsed->setYear($year)->startOfDay();
    }
}
