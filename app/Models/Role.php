<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_system_role',
        'level',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system_role' => 'boolean',
        'level' => 'integer',
    ];

    // System role constants
    const SUPER_ADMIN = 'super-admin';
    const ADMIN = 'admin';
    const EDITOR = 'editor';
    const AUTHOR = 'author';
    const CONTRIBUTOR = 'contributor';
    const SUBSCRIBER = 'subscriber';

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function hasPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions) || in_array('*', $permissions);
    }

    public function grantPermission($permission)
    {
        $permissions = $this->permissions ?? [];

        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }

        return $this;
    }

    public function revokePermission($permission)
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->permissions = array_values($permissions);
        $this->save();

        return $this;
    }

    public function syncPermissions(array $permissions)
    {
        $this->permissions = $permissions;
        $this->save();

        return $this;
    }

    public static function getSystemRoles()
    {
        return [
            self::SUPER_ADMIN => [
                'name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'permissions' => ['*'],
                'level' => 100,
            ],
            self::ADMIN => [
                'name' => 'Administrator',
                'description' => 'Site administration with most permissions',
                'permissions' => [
                    'manage_users',
                    'manage_roles',
                    'manage_content',
                    'manage_media',
                    'manage_themes',
                    'manage_plugins',
                    'manage_settings',
                    'view_analytics',
                ],
                'level' => 90,
            ],
            self::EDITOR => [
                'name' => 'Editor',
                'description' => 'Content management and moderation',
                'permissions' => [
                    'manage_content',
                    'manage_media',
                    'moderate_comments',
                    'view_analytics',
                ],
                'level' => 70,
            ],
            self::AUTHOR => [
                'name' => 'Author',
                'description' => 'Create and manage own content',
                'permissions' => [
                    'create_content',
                    'edit_own_content',
                    'delete_own_content',
                    'upload_media',
                ],
                'level' => 50,
            ],
            self::CONTRIBUTOR => [
                'name' => 'Contributor',
                'description' => 'Create content for review',
                'permissions' => [
                    'create_content',
                    'edit_own_content',
                ],
                'level' => 30,
            ],
            self::SUBSCRIBER => [
                'name' => 'Subscriber',
                'description' => 'Basic user access',
                'permissions' => [
                    'view_content',
                    'comment',
                ],
                'level' => 10,
            ],
        ];
    }

    public static function createSystemRoles()
    {
        $systemRoles = self::getSystemRoles();

        foreach ($systemRoles as $slug => $roleData) {
            self::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'permissions' => $roleData['permissions'],
                    'is_system_role' => true,
                    'level' => $roleData['level'],
                ]
            );
        }
    }

    public static function getAllPermissions()
    {
        return [
            // User Management
            'manage_users' => 'Manage Users',
            'create_users' => 'Create Users',
            'edit_users' => 'Edit Users',
            'delete_users' => 'Delete Users',
            'view_users' => 'View Users',

            // Role Management
            'manage_roles' => 'Manage Roles',
            'create_roles' => 'Create Roles',
            'edit_roles' => 'Edit Roles',
            'delete_roles' => 'Delete Roles',
            'assign_roles' => 'Assign Roles',

            // Content Management
            'manage_content' => 'Manage All Content',
            'create_content' => 'Create Content',
            'edit_content' => 'Edit All Content',
            'edit_own_content' => 'Edit Own Content',
            'delete_content' => 'Delete All Content',
            'delete_own_content' => 'Delete Own Content',
            'publish_content' => 'Publish Content',
            'view_content' => 'View Content',

            // Media Management
            'manage_media' => 'Manage Media Library',
            'upload_media' => 'Upload Media',
            'edit_media' => 'Edit Media',
            'delete_media' => 'Delete Media',

            // Theme Management
            'manage_themes' => 'Manage Themes',
            'install_themes' => 'Install Themes',
            'activate_themes' => 'Activate Themes',
            'customize_themes' => 'Customize Themes',

            // Plugin Management
            'manage_plugins' => 'Manage Plugins',
            'install_plugins' => 'Install Plugins',
            'activate_plugins' => 'Activate Plugins',
            'configure_plugins' => 'Configure Plugins',

            // Comment Management
            'moderate_comments' => 'Moderate Comments',
            'comment' => 'Post Comments',

            // System Settings
            'manage_settings' => 'Manage System Settings',
            'view_analytics' => 'View Analytics',
            'manage_seo' => 'Manage SEO Settings',

            // Menu Management
            'manage_menus' => 'Manage Navigation Menus',

            // Widget Management
            'manage_widgets' => 'Manage Widgets',
        ];
    }

    public function canManageRole(Role $role)
    {
        // Super admin can manage all roles
        if ($this->slug === self::SUPER_ADMIN) {
            return true;
        }

        // Cannot manage roles with higher or equal level
        return $this->level > $role->level;
    }

    public function scopeSystemRoles($query)
    {
        return $query->where('is_system_role', true);
    }

    public function scopeCustomRoles($query)
    {
        return $query->where('is_system_role', false);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeMinLevel($query, $minLevel)
    {
        return $query->where('level', '>=', $minLevel);
    }

    public function scopeMaxLevel($query, $maxLevel)
    {
        return $query->where('level', '<=', $maxLevel);
    }
}