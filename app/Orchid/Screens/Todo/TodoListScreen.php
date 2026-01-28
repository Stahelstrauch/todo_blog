<?php

namespace App\Orchid\Screens\Todo;

use App\Models\Todo;
use App\Orchid\Layouts\Todo\TodoListLayout;
use Illuminate\Support\Facades\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class TodoListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $filters = (array) $request->get('filter', []);

        $query = Todo::query()->orderByDesc('created_at');

        // Nime filter:otsib ainult name väljast
        if(!empty($filters['name'])) {
            // SELECT * FROM todos WHERE name LIKE '%fraas%' ORDER BY created_at Desc
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        // Kas on tehtud filter 1 või 0
        if(isset($filers['done']) && $filters['done'] !== '') {
            $query->where('is_done', (int) $filters['done']);
        }

        return [
            'todos' => Todo::filters()->defaultSort('created_at', 'desc')->paginate(5)->withQueryString(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Todo ListScreen';
    }

    public function description(): ?string
    {
        return 'Ülesannete nimekiri ja haldus.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Lisa uus')
            ->route('platform.todos.create')
            ->icon('bs.plus-circle'),
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
            TodoListLayout::class,
        ];
    }

    public function removeFromList(Todo $todo) {
        $todo->delete();

        Toast::info('Todo kustutatud!');

        return redirect()->route('platform.todos');
    }

    public function permission(): ?iterable
    {
        return [
            'platform.todos',
        ];
    }
}
