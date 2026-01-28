<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class TodoRequest
 *
 * Valideerib ToDo lisamise/muutmise vormi andmed (Orchid Screen).
 * Orchid vorm saadab andmed struktuuris: todo[name], todo[description], todo[is_done], todo[due_at]
 */
class TodoRequest extends FormRequest {
    /**
     * Kas kasutajal on õigus seda päringut teha.
     * Kui Orchidis on õigused paigas, siis see on lisakaitse.
     */
    public function authorize(): bool {
        // return auth()->check() && auth()->user()->hasAccess('platform.todos');
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->hasAccess('platform.todos');
    }

    /**
     * Valideerimisreeglid.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'todo.name'        => ['required', 'string', 'min:2', 'max:150'],
            'todo.description' => ['nullable', 'string', 'max:5000'],
            'todo.is_done'     => ['nullable', 'boolean'],
            'todo.due_at'      => ['nullable', 'date'],
        ];
    }
    /**
     * Eesti keelsed veateated.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'todo.name.required' => 'Nimi on kohustuslik.',
            'todo.name.string'   => 'Nimi peab olema tekst.',
            'todo.name.min'      => 'Nimi peab olema vähemalt :min tähemärki.',
            'todo.name.max'      => 'Nimi võib olla maksimaalselt :max tähemärki.',

            'todo.description.string' => 'Kirjeldus peab olema tekst.',
            'todo.description.max'    => 'Kirjeldus võib olla maksimaalselt :max tähemärki.',

            'todo.is_done.boolean' => 'Tehtud väärtus peab olema jah/ei.',

            'todo.due_at.date' => 'Tähtaeg peab olema korrektne kuupäev.',
        ];
    }

    /**
     * Inimloetavad väljade nimed veateadetes.
     *
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'todo.name'        => 'nimi',
            'todo.description' => 'kirjeldus',
            'todo.is_done'     => 'tehtud',
            'todo.due_at'      => 'tähtaeg',
        ];
    }
}
