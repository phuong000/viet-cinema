<?php
declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public static function errors(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $constraints) {
            $value = $data[$field] ?? '';
            if (!is_string($value)) {
                $errors[$field] = 'Dữ liệu phải là chuỗi văn bản.';
                continue;
            }
            foreach ($constraints as $rule) {
                [$name, $limit] = array_pad(explode(':', $rule, 2), 2, null);
                $invalid = match ($name) {
                    'required' => trim($value) === '',
                    'email' => $value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false,
                    'min' => strlen($value) < (int) $limit,
                    'max' => strlen($value) > (int) $limit,
                    default => throw new \InvalidArgumentException('Unknown validation rule.'),
                };
                if ($invalid) {
                    $errors[$field] = 'Giá trị không hợp lệ (' . $rule . ').';
                    break;
                }
            }
        }
        return $errors;
    }
}
