<?php

declare(strict_types=1);

namespace Liberu\Cms\Menus\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Cms\Menus\Database\Factories\MenuItemFactory;

/**
 * A single link in a menu. May nest via parent_id, and may require a permission
 * to be shown (role/permission-based menus).
 *
 * @property int $id
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string $label
 * @property string $url
 * @property int $sort
 * @property string|null $permission
 */
final class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;

    #[\Override]
    protected $table = 'cms_menu_items';

    /**
     * @var list<string>
     */
    #[\Override]
    protected $fillable = ['menu_id', 'parent_id', 'label', 'url', 'sort', 'permission'];

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return ['sort' => 'integer'];
    }

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    protected static function newFactory(): MenuItemFactory
    {
        return MenuItemFactory::new();
    }
}
