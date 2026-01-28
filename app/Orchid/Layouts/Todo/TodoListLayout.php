<?php

namespace App\Orchid\Layouts\Todo;

use App\Models\Todo;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TodoListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'todos';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('name', 'Nimi')
                ->sort()
                ->filter(Input::make()->placeholder('Otsi nimest...')),
            
            TD::make('is_done', 'Tehtud')
                ->sort()
                ->align(TD::ALIGN_CENTER)
                ->filter(
                    Select::make('is_done')
                        ->options([
                            '1' => 'Jah',
                            '0' => 'Ei',
                        ])->empty('Kõik')
                )->render(function(Todo $todo){
                    if($todo->is_done){
                        return '<span class="px-2 py-1 rounded bg-success text-white">Jah</span>';
                    } 
                    return '<span class="px-2 py-1 rounded bg-danger text-white">Ei</span>';
                }),

            TD::make('due_at', 'Tähtaeg')
                ->sort()
                ->render(function(Todo $todo){
                    if(!$todo->due_at){
                        return '-';
                    }

                    $date = $todo->due_at->format('d.m.Y');

                    // Kollane: Tähtaeg möödas + tegema
                    if($todo->due_at->lt(now()->startOfDay()) && !$todo->is_done) {
                        return '<span class="px-1 py-1 rounded bg-warning text-white">'.e($date). '</span>';
                    }
                    return e($date);
                }),

            TD::make('created_at', 'Loodud')
                ->sort()
                ->render(function (Todo $todo){
                    return $todo->created_at->format('d.m.Y') ?? '-';
                }),

            TD::make('updated_at', 'Uuendatud')
                ->sort()
                ->render(function (Todo $todo){
                    if(!$todo->updated_at){
                        return '-';
                    }
                    $updated = $todo->updated_at->format('d.m.Y H:i:s');

                    return $updated;
                }),    

            TD::make('action', 'Tegevused')
                ->align(TD::ALIGN_CENTER)
                ->width('120px')
                ->render(function(Todo $todo) {
                    return'<dic class"d-flex justify-content-center gap-1">' 
                    . 
                    Link::make('')
                        ->route('platform.todos.edit', $todo->id)
                        ->icon('bs.pensil')
                    .

                    Button::make('')
                        ->icon('bs.trash')
                        ->confirm('Kas oled kindel, et soovid kustutada?')
                        ->method('removeFromList', [
                            'todo' => $todo->id,
                        ])
                        .'</div>';
                }),  

        ];
    }
}
