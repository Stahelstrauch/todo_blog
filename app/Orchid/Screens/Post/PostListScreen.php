<?php

namespace App\Orchid\Screens\Post;

use App\Models\Post;
use Illuminate\Support\HtmlString;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PostListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            // TODO autori jama on vaja korda teha
            'posts' => Post::with('author')->latest('id')->paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Postitused';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Loo postitus')
                ->route('platform.posts.create'),

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
            Layout::table('posts', [
                TD::make('title', 'Pealkiri')->sort(),
                TD::make('published_at', 'Olek')
                    ->sort()
                    ->render(function(Post $p) {
                        //Mustand
                        if($p->published_at === null) {
                            return new HtmlString(
                                '<span class="badge bg-secondary">'.e($p->published_at_date).'</span>'
                            );
                        }
                        // Avaldub tulevikus
                        if($p->published_at->isFuture()) {
                            return new HtmlString(
                                '<span class="badge bg-warning text-dark">Avaldatakse '.e($p->published_at_date).'</span'
                            );
                        }
                         
                        // Avaldatud
                        return new HtmlString(
                            '<span class="badge bg-success"> Avaldatud '.e($p->published_at_date).'</span>'
                        );
                    })
            ])
        ];
    }
}
