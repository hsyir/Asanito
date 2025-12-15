<?php

namespace App\Services;

class UtilService {


public function normalizeIranPhone(?string $number): ?string
{
    if (!$number) return null;

    // فقط عدد
    $number = preg_replace('/\D/', '', $number);

    // حذف پیشوند کشور
    if (str_starts_with($number, '0098')) {
        $number = substr($number, 4);
    } elseif (str_starts_with($number, '98')) {
        $number = substr($number, 2);
    }

    // اگر 12 رقمی بود و با 9 شروع می‌شد → 9 اضافی
    if (strlen($number) === 12 && $number[0] === '9') {
        $number = substr($number, 1);
    }

    // اگر 10 رقمی و با 9 شروع می‌شد
    if (strlen($number) === 10 && $number[0] === '9') {
        return '0' . $number;
    }

    // اگر از قبل درست بود
    if (strlen($number) === 11 && str_starts_with($number, '09')) {
        return $number;
    }

    return null;
}




}