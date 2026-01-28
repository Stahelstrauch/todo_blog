<?php

namespace App\Orchid\Layouts\Todo;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class TodoEditLayout extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        return [
            Input::make('todo.name')
                ->title('Nimi')
                ->placeholder('Nt. Osta piima')
                ->required()
                ->maxlength(150),

            TextArea::make('todo.description')
                ->title('Kirjeldus')
                ->rows(5)
                ->placeholder('Soovi korral lisa täpsustus (pole kohustuslik).'),

            CheckBox::make('todo.is_done')
                ->title('Tehtud')
                ->sendTrueOrFalse()
                ->help('Märgista, kui ülesanne on tehtud'),

            DateTimer::make('todo.due_at')
                ->title('Tähtaeg')
                ->allowInput()
                ->format('Y-m-d')
                ->placeholder('Valikuline')

        ];
    }
}
