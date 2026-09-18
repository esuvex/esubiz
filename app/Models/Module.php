<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'catalog_product_id',
        'uuid',
        'name',
        'slug',
        'description',
        'developer_id',
        'type',
        'version',
        'package_name',
        'sidebar_menu_name',
        'saas_wizard_visible',
        'off_server_wizard_visible',
        'compatible_website_types',
        'compatible_themes',
        'core_function_configuration',
        'marketplace_metadata',
        'requirements',
        'is_verified',
        'is_active',
    ];

    protected $casts = [
        'saas_wizard_visible' => 'boolean',
        'off_server_wizard_visible' => 'boolean',
        'compatible_website_types' => 'array',
        'compatible_themes' => 'array',
        'core_function_configuration' => 'array',
        'marketplace_metadata' => 'array',
        'requirements' => 'array',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function catalogProduct()
    {
        return $this->belongsTo(
            CatalogProduct::class,
            'catalog_product_id'
        );
    }

    public function developer()
    {
        return $this->belongsTo(
            User::class,
            'developer_id'
        );
    }

    public function assets()
    {
        return $this->hasMany(ModuleAsset::class);
    }

    public function versions()
    {
        return $this->hasMany(ModuleVersion::class);
    }

    public function prices()
    {
        return $this->hasMany(ModulePrice::class);
    }

    public function capabilities()
    {
        return $this->hasMany(ModuleCapability::class);
    }

    public function dependencies()
    {
        return $this->hasMany(ModuleDependency::class);
    }

    public function permissions()
    {
        return $this->hasMany(ModulePermission::class);
    }

    public function reviews()
    {
        return $this->hasMany(ModuleReview::class);
    }

    public function installations()
    {
        return $this->hasMany(ModuleInstallation::class);
    }
}
