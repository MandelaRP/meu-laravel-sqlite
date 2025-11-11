<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];
    
    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        // Se o valor é "0" (string), retornar 0, não o default
        $value = match ($setting->type) {
            'decimal', 'float' => (float) $setting->value,
            'integer', 'int' => (int) $setting->value,
            'boolean', 'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
        
        // Se o valor é 0 (numérico) ou "0" (string), retornar o valor, não o default
        if ($value === 0 || $value === '0' || $value === 0.0) {
            return $value;
        }
        
        // Se o valor existe mas é null ou vazio, retornar o default
        if ($value === null || $value === '') {
            return $default;
        }
        
        return $value;
    }
    
    /**
     * Set setting value by key
     */
    public static function set(string $key, $value, string $type = 'string', ?string $description = null): void
    {
        $setting = self::firstOrNew(['key' => $key]);
        $setting->value = is_array($value) ? json_encode($value) : (string) $value;
        $setting->type = $type;
        if ($description) {
            $setting->description = $description;
        }
        $setting->save();
    }
}
