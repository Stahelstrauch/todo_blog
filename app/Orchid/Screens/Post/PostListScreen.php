<?php

namespace App\Orchid\Screens\Post;

use App\Models\Post;
use Illuminate\Support\HtmlString;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

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
                ->render(function (Post $p) {
                    if ($p->published_at === null) {
                        return new HtmlString(
                            '<span class="badge bg-secondary">Mustand</span>'
                        );
                    }

                    if ($p->published_at->isFuture()) {
                        return new HtmlString(
                            '<span class="badge bg-warning text-dark">
                                Avaldatakse ' . e($p->published_at_date) . '
                            </span>'
                        );
                    }

                    return new HtmlString(
                        '<span class="badge bg-success">
                            Avaldatud ' . e($p->published_at_date) . '
                        </span>'
                    );
                }),

            TD::make('created_at', 'Loodud')
                ->sort()
                ->render(fn (Post $p) => $p->created_at_formatted ?? ''),

            TD::make('action', 'Tegevused')
                ->align(TD::ALIGN_CENTER)
                ->width('120px')
                ->render(function(Post $p) {
                    return
                    '<div class="d-flex justify-content-center gap-1">'
                    .
                    Link::make('')
                        ->route('platform.posts.edit', $p->id)
                        ->icon('bs.pencil')
                        .
                        Button::make('')
                        ->icon('bs.trash')
                        ->confirm('Kas oled kindel, et soovid kustutada?')
                        ->method('remove', ['post' => $p->id])
                        .'</div>';
                }),
        ])
    ];
    
}
   private function deleteFeaturedIfExists(?string $path) {
        if(empty($path)) {
            return;
        }

        $rel = $this->toPublicDiskRelative($path);
        if(!empty($rel)) {
            Storage::disk('public')->delete($rel);
        }
    }

    private function toPublicDiskRelative(?string $path) {
        if(empty($path)) {
            return;
        }

        $p = ltrim($path, '/');

        if(str_starts_with($p, 'storage/')) {
            $p = substr($p, strlen('storage/'));
        }
        return $p;
    }

    public function remove(Post $post) {
        $this->deleteFeaturedIfExists($post->featured_image_path);
        $post->delete();

        Toast::info('Postitus kustutatud!');
        return redirect()->route('platform.posts');
    }



}
