<?php

namespace App\Orchid\Screens\Settings;

use App\Models\Setting;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ReactionSettingsScreen extends Screen
{

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'enabled' => (bool) (int) Setting::get('reactions.enabled', 1),
            'allow_change' => (bool) (int) Setting::get('reactions.allow_change', 1),
            'cooldown_minutes' => (int) Setting::get('reactions.cooldown_minutes', 60),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Reaktsioonide seaded';
    }

    public function description(): ?string
    {
        return 'Reaktsioonide globaalne lubamine ja muutmise piirangud.';
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
                ->icon('bs.check-circle')
                ->type(Color::SUCCESS),
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
                Switcher::make('enabled')
                    ->title('Reaktsioonid lubatud')
                    ->sendTrueOrFalse()
                    ->help('Kui välja lülitada, ei saa keegi reageerida.'),

                Switcher::make('allow_change')
                    ->title('Reaktsiooni muutmine lubatud')
                    ->sendTrueOrFalse()
                    ->help('Välja lülitades, saab kasutaa ainult korra reageerida.'),

                Input::make('cooldown_minutes')
                    ->type('number')
                    ->min(0)
                    ->step(1)
                    ->title('Muutmise ooteaeg (minutites')
                    ->help('Muutmise lubamise puhul - mitme minuti pärast saab muuta.')   // 0 = piiran puudub 
            ]),
        ];
    }

    public function save(Request $request) {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'allow_change' => ['required', 'boolean'],
            'cooldown_minutes' => ['required', 'integer', 'min:0', 'max:10080'],
        ]);

        Setting::set('reactions.enabled', (int) $data['enabled']);
        Setting::set('reactions.allow_change', (int) $data['allow_change']);
        Setting::set('reactions.cooldown_minutes', (int) $data['cooldown_minutes']);

        Toast::info('Reaktsioonide seaded salvestatud!');

        return redirect()->route('platform.settings.reactions');
    }
}
