<?php

namespace App\Orchid\Screens\Settings;

use App\Models\Setting;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CommentSettingsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'comments' => [
                'enabled' => (bool) Setting::get('comments.enabled', true),
                'user_cooldown_minutes' => (int) Setting::get('comments.user_cooldown_minutes', 5),

            ],
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Kommenteerimise seaded';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Salvesta')
                ->method('save')
                ->icon('bs.check-circle'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::rows([
                Switcher::make('comments.enabled')
                    ->sendTrueOrFalse()
                    ->title('Kommenteerimine lubatud')
                    ->help('Lülitab kommentaaride lisamise kogu saidil sisse või välja.'),

                Input::make('comments.user_cooldown_minutes')
                    ->type('number')
                    ->min(1)
                    ->title('Globaalne piirang (minutites')
                    ->help('Üks kommentaar kasutaja kohta iga x minuti tagant kogu saidil.'),
            ]),


        ];
    }
    public function save(Request $request) {
        $data = $request->validate([
            'comments.enabled' => ['required', 'boolean'],
            'comments.user_cooldown_minutes' => ['required', 'integer', 'min:1'],
        ]);

        Setting::set('comments.enabled', (bool) $data['comments']['enabled']);
        Setting::set('comments.user_cooldown_minutes', (int) $data['comments']['user_cooldown_minutes']);

        Toast::info('Kommenteerimise seaded salvestatud.');
    }
}
