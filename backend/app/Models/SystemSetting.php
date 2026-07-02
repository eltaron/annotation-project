<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected $table = 'system_settings';

    public static function getValue($key, $default = null)
    {
        return Cache::remember("system_setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function setValue($key, $value)
    {
        $setting = static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("system_setting.{$key}");
        return $setting;
    }

    public static function getAll()
    {
        return static::pluck('value', 'key')->toArray();
    }

    public static function clearCache()
    {
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("system_setting.{$key}");
        }
    }
}
