<?php

namespace App\Orchid\Screens\Todo;

use App\Http\Requests\TodoRequest;
use App\Models\Todo;
use App\Orchid\Layouts\Todo\TodoEditLayout;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class TodoEditScreen extends Screen
{
    /**
     * Hetkel ekraanil olev Todo mudel
     * Nullable, sest create vaates pole alguses olemaolevat kirjet
     */
    public ?Todo $todo = null;

    public string $name = 'ToDo';

    public string $description = 'Ülesannete nimekiri ja haldus.';
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(?Todo $todo = null): iterable
    {
        $todo = $todo ?? new Todo();

        $this->todo = $todo;

        $this->name = $todo->exists ? 'Muuda ToDo' : 'Lisa ToDo';
        $this->description = $todo->exists ? 'Muuda olemaolevat ülesannet' : 'Lisa uus ülesanne';


        return ['todo' => $todo];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    // public function name(): ?string
    // {
    //     return 'Todo EditScreen';
    // }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Tagasi')
                ->route('platform.todos')
                ->icon('bs.arrow-left-circle'),

            Button::make('Salvesta')
                ->icon('bs.check-circle')
                ->method('save'),
                
            Button::make('Kustuta')
                ->icon('bs.trash')
                ->confirm('Kas oled kindel, et soovid seda kustutada?')
                ->method('remove')
                ->canSee( (bool) ($this->todo?->exists)),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [TodoEditLayout::class];
    }

    // Save nupu funktsioon
    public function save(TodoRequest $request) {
        $validated = $request->validated();
        $data = $validated['todo'] ?? [];

        $todo = $this->todo ?? new Todo();
        $todo->fill($data);
        $todo->save();

        Toast::info('Salvestatud!');

        return redirect()->route('platform.todos');
    }

    // Eemaldamise nupu funktsioon
    public function remove() {
        if(!($this->todo?->exists)) {
            return redirect()->route('platform.todos');
        }
        $this->todo->delete();

        Toast::info('Kustutatud');

        return redirect()->route('platform.todos');
    }
}
