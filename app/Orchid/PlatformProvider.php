<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\Post;
use App\Models\Todo;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Get Started')
                ->icon('bs.book')
                ->title('Navigation')
                ->route(config('platform.index')),

            // Menu::make('Sample Screen')
            //     ->icon('bs.collection')
            //     ->route('platform.example')
            //     ->badge(fn () => 6),

            // Menu::make('Form Elements')
            //     ->icon('bs.card-list')
            //     ->route('platform.example.fields')
            //     ->active('*/examples/form/*'),

            // Menu::make('Layouts Overview')
            //     ->icon('bs.window-sidebar')
            //     ->route('platform.example.layouts'),

            // Menu::make('Grid System')
            //     ->icon('bs.columns-gap')
            //     ->route('platform.example.grid'),

            // Menu::make('Charts')
            //     ->icon('bs.bar-chart')
            //     ->route('platform.example.charts'),

            // Menu::make('Cards')
            //     ->icon('bs.card-text')
            //     ->route('platform.example.cards')
            //     ->divider(),

            Menu::make('ToDo Nimekiri')
                ->icon('bs.check2-square')
                ->route('platform.todos')
                ->permission('platform.todos')
                ->title('Ise loodud')->badge(
                    function() {
                        $total = Todo::count();
                        $open = Todo::where('is_done', false)->count();
                        return "{$open} / {$total}";
                    }
                ),

            Menu::make('Postitused')
                ->icon('bs.file-text')
                ->route('platform.posts')
                ->permission('platform.posts')
                ->badge(function() {
                    $today = now()->toDateString();

                    $stats = Post::selectRaw(
                        'COUNT(*) as total, SUM(CASE WHEN published_at IS NOT NULL AND DATE(published_at) <= ? THEN 1 ELSE 0 END) as published',
                        [$today])->first();
                    
                    $published = (int) ($stats->published ?? 0);
                    $total = (int) ($stats->total ?? 0);
                    $notpublished = $total - $published;

                    return "{$notpublished} / {$published} / {$total}";
                }),

            Menu::make('Kommentaarid')
                ->title('Seaded')
                ->icon('bs.chat-dots')
                ->route('platform.settings.comments')    
                ->permission('platform.settings.comments'),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make('Documentation')
                ->title('Docs')
                ->icon('bs.box-arrow-up-right')
                ->url('https://orchid.software/en/docs')
                ->target('_blank'),

            Menu::make('Changelog')
                ->icon('bs.box-arrow-up-right')
                ->url('https://github.com/orchidsoftware/platform/blob/master/CHANGELOG.md')
                ->target('_blank')
                ->badge(fn () => Dashboard::version(), Color::DARK),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),

            ItemPermission::group('Ise loodud')
                ->addPermission('platform.todos', 'ToDo Haldamine'),

            ItemPermission::group('Blogi')
            //TODO Kolm veel tulevikus juurde
                ->addPermission('platform.posts', 'Postituste haldus'),

            ItemPermission::group('Seaded')  
                ->addPermission('platform.settings.comments', 'Kommentaarid'),  
        ];
    }
}
