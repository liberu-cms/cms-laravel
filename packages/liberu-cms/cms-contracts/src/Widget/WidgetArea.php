<?php

declare(strict_types=1);

namespace Liberu\Cms\Contracts\Widget;

/**
 * Where a widget renders (Part B §12).
 */
enum WidgetArea: string
{
    case Sidebar = 'sidebar';
    case Dashboard = 'dashboard';
    case Footer = 'footer';
}
